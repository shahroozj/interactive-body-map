/**
 * Packaging script.
 *
 *   node package.mjs
 *
 * Produces the two files a customer actually receives:
 *
 *   dist/interactive-body-map-wordpress-<version>.zip
 *   dist/interactive-body-map-javascript-<version>.zip
 *
 * Run `node build.mjs` first, or use `npm run package`, which does both.
 *
 * The archive is written here rather than shelled out to, for two reasons:
 * PowerShell's Compress-Archive writes Windows path separators into the entry
 * names, which the ZIP format does not allow and some unzippers refuse; and
 * fixing the timestamps makes the output byte-for-byte reproducible.
 */

import { mkdirSync, readFileSync, readdirSync, statSync, writeFileSync } from 'node:fs';
import { dirname, join, relative, sep } from 'node:path';
import { fileURLToPath } from 'node:url';
import { deflateRawSync } from 'node:zlib';

const root = dirname(fileURLToPath(import.meta.url));
const dist = join(root, 'dist');

const version = /^\s*\*\s*Version:\s*(.+)$/m.exec(
    readFileSync(join(root, 'wordpress-plugin', 'interactive-body-map', 'interactive-body-map.php'), 'utf8')
)[1].trim();

/* ---------------------------------------------------------------------- *
 * A small, correct ZIP writer.
 * ---------------------------------------------------------------------- */

const CRC_TABLE = (() => {
    const table = new Int32Array(256);
    for (let i = 0; i < 256; i++) {
        let c = i;
        for (let k = 0; k < 8; k++) { c = c & 1 ? 0xedb88320 ^ (c >>> 1) : c >>> 1; }
        table[i] = c;
    }
    return table;
})();

function crc32(buffer) {
    let c = -1;
    for (let i = 0; i < buffer.length; i++) {
        c = CRC_TABLE[(c ^ buffer[i]) & 0xff] ^ (c >>> 8);
    }
    return (c ^ -1) >>> 0;
}

// A fixed date keeps successive builds identical: 2024-01-01 00:00:00.
const DOS_TIME = 0;
const DOS_DATE = ((2024 - 1980) << 9) | (1 << 5) | 1;

function zipFiles(files) {
    const locals = [];
    const central = [];
    let offset = 0;

    for (const { name, data } of files) {
        const nameBytes = Buffer.from(name, 'utf8');
        const compressed = deflateRawSync(data, { level: 9 });
        // Storing is better than inflating a file that does not compress.
        const useDeflate = compressed.length < data.length;
        const body = useDeflate ? compressed : data;
        const method = useDeflate ? 8 : 0;
        const crc = crc32(data);

        const local = Buffer.alloc(30);
        local.writeUInt32LE(0x04034b50, 0);
        local.writeUInt16LE(20, 4);          // version needed
        local.writeUInt16LE(0, 6);           // flags
        local.writeUInt16LE(method, 8);
        local.writeUInt16LE(DOS_TIME, 10);
        local.writeUInt16LE(DOS_DATE, 12);
        local.writeUInt32LE(crc, 14);
        local.writeUInt32LE(body.length, 18);
        local.writeUInt32LE(data.length, 22);
        local.writeUInt16LE(nameBytes.length, 26);
        local.writeUInt16LE(0, 28);          // extra field length

        locals.push(local, nameBytes, body);

        const entry = Buffer.alloc(46);
        entry.writeUInt32LE(0x02014b50, 0);
        entry.writeUInt16LE(20, 4);          // version made by
        entry.writeUInt16LE(20, 6);          // version needed
        entry.writeUInt16LE(0, 8);           // flags
        entry.writeUInt16LE(method, 10);
        entry.writeUInt16LE(DOS_TIME, 12);
        entry.writeUInt16LE(DOS_DATE, 14);
        entry.writeUInt32LE(crc, 16);
        entry.writeUInt32LE(body.length, 20);
        entry.writeUInt32LE(data.length, 24);
        entry.writeUInt16LE(nameBytes.length, 28);
        entry.writeUInt16LE(0, 30);          // extra
        entry.writeUInt16LE(0, 32);          // comment
        entry.writeUInt16LE(0, 34);          // disk number
        entry.writeUInt16LE(0, 36);          // internal attributes
        entry.writeUInt32LE(0o644 << 16, 38); // external attributes
        entry.writeUInt32LE(offset, 42);

        central.push(entry, nameBytes);
        offset += local.length + nameBytes.length + body.length;
    }

    const directory = Buffer.concat(central);

    const end = Buffer.alloc(22);
    end.writeUInt32LE(0x06054b50, 0);
    end.writeUInt16LE(0, 4);
    end.writeUInt16LE(0, 6);
    end.writeUInt16LE(files.length, 8);
    end.writeUInt16LE(files.length, 10);
    end.writeUInt32LE(directory.length, 12);
    end.writeUInt32LE(offset, 16);
    end.writeUInt16LE(0, 20);

    return Buffer.concat([...locals, directory, end]);
}

/* ---------------------------------------------------------------------- *
 * Collect and write.
 * ---------------------------------------------------------------------- */

const SKIP = /^(node_modules|\.git|dist|\.DS_Store|Thumbs\.db)$/;

/**
 * Walks a directory, returning entries named relative to `base` and placed
 * under `prefix` so the archive extracts into one folder rather than scattering.
 */
function collect(dir, base, prefix, out = []) {
    for (const entry of readdirSync(dir, { withFileTypes: true }).sort((a, b) => a.name.localeCompare(b.name))) {
        if (SKIP.test(entry.name)) { continue; }

        const full = join(dir, entry.name);
        if (entry.isDirectory()) {
            collect(full, base, prefix, out);
        } else {
            out.push({
                // ZIP entry names always use forward slashes.
                name: [prefix, ...relative(base, full).split(sep)].join('/'),
                data: readFileSync(full)
            });
        }
    }
    return out;
}

mkdirSync(dist, { recursive: true });

const bundles = [
    // WordPress requires the plugin folder at the root of the zip.
    ['wordpress', join(root, 'wordpress-plugin', 'interactive-body-map'), 'interactive-body-map'],
    ['javascript', join(root, 'js-version'), 'interactive-body-map-javascript']
];

console.log(`Packaging version ${version}\n`);

for (const [name, source, prefix] of bundles) {
    const files = collect(source, source, prefix);
    const out = join(dist, `interactive-body-map-${name}-${version}.zip`);

    writeFileSync(out, zipFiles(files));

    const kb = (statSync(out).size / 1024).toFixed(1);
    console.log(`  ${name.padEnd(11)} ${String(files.length).padStart(2)} files  ->  ${kb.padStart(6)} KB`);
    console.log(`              ${relative(root, out)}`);
    for (const file of files) { console.log(`                ${file.name}`); }
    console.log('');
}

console.log('Done.');
