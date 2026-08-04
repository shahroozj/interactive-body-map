<?php
/**
 * Figure geometry.
 *
 * GENERATED FILE - do not edit by hand.
 * Produced by build.mjs from js-version/js/body-map.js, which is the one
 * place the drawing is defined. Re-run `node build.mjs` after changing it.
 *
 * @package Interactive_Body_Map
 */

defined( 'ABSPATH' ) || exit;

/**
 * The paths that make up the figure, plus the naming tables that go with them.
 */
final class BodyMap_Geometry {

	/** SVG viewBox for the whole figure. */
	const VIEW_BOX = '30 0 340 1000';

	/** Transform applied to parts on the subject's left. */
	const MIRROR = 'matrix(-1,0,0,1,400,0)';

	/**
	 * Every drawable part, in drawing order.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public static function parts() {
		return array(
			array(
				'id'     => 'head',
				'label'  => __( 'Head', 'interactive-body-map' ),
				'group'  => 'head',
				'mirror' => false,
				'd'      => 'M200,20 C227,20 247,43 247,76 C247,92 244,104 240,113 C234,126 218,140 200,140 C182,140 166,126 160,113 C156,104 153,92 153,76 C153,43 173,20 200,20 Z',
			),
			array(
				'id'     => 'neck',
				'label'  => __( 'Neck', 'interactive-body-map' ),
				'group'  => 'neck',
				'mirror' => false,
				'd'      => 'M177,127 C177,143 177,155 175,163 C174,167 173,169 172,171 L228,171 C227,169 226,167 225,163 C223,155 223,143 223,127 C215,137 208,140 200,140 C192,140 185,137 177,127 Z',
			),
			array(
				'id'     => 'chest',
				'label'  => __( 'Chest', 'interactive-body-map' ),
				'group'  => 'chest',
				'mirror' => false,
				'd'      => 'M172,171 C160,176 149,189 142,206 C133,232 126,264 124,292 C123,312 122,334 123,352 C150,344 174,338 200,338 C226,338 250,344 277,352 C278,334 277,312 276,292 C274,264 267,232 258,206 C251,189 240,176 228,171 Z',
			),
			array(
				'id'     => 'abdomen',
				'label'  => __( 'Abdomen', 'interactive-body-map' ),
				'group'  => 'abdomen',
				'mirror' => false,
				'd'      => 'M123,352 C126,368 129,384 130,400 C131,416 128,428 126,434 C150,442 175,446 200,446 C225,446 250,442 274,434 C272,428 269,416 270,400 C271,384 274,368 277,352 C250,344 226,338 200,338 C174,338 150,344 123,352 Z',
			),
			array(
				'id'     => 'pelvis',
				'label'  => __( 'Pelvis', 'interactive-body-map' ),
				'group'  => 'pelvis',
				'mirror' => false,
				'd'      => 'M126,434 C118,450 111,466 110,488 C109,510 111,530 114,545 L176,545 C183,542 190,536 196,528 C198,532 202,532 204,528 C210,536 217,542 224,545 L286,545 C289,530 291,510 290,488 C289,466 282,450 274,434 C250,442 225,446 200,446 C175,446 150,442 126,434 Z',
			),
			array(
				'id'     => 'shoulder-right',
				'label'  => __( 'Right shoulder', 'interactive-body-map' ),
				'group'  => 'arm-right',
				'mirror' => false,
				'd'      => 'M142,206 C122,208 105,215 95,232 C85,250 77,272 77,296 C77,306 77,312 78,320 L118,322 C119,310 121,300 124,292 C127,262 133,232 142,206 Z',
			),
			array(
				'id'     => 'upper-arm-right',
				'label'  => __( 'Right upper arm', 'interactive-body-map' ),
				'group'  => 'arm-right',
				'mirror' => false,
				'd'      => 'M78,320 L118,322 C118,346 118,368 117,386 C117,396 117,402 117,408 L81,410 C80,400 79,390 79,378 C78,358 78,340 78,320 Z',
			),
			array(
				'id'     => 'forearm-right',
				'label'  => __( 'Right forearm', 'interactive-body-map' ),
				'group'  => 'arm-right',
				'mirror' => false,
				'd'      => 'M81,410 L117,408 C117,432 116,456 114,478 C113,496 113,508 113,520 L87,522 C85,508 83,494 82,476 C79,452 77,431 81,410 Z',
			),
			array(
				'id'     => 'hand-right',
				'label'  => __( 'Right hand', 'interactive-body-map' ),
				'group'  => 'arm-right',
				'mirror' => false,
				'd'      => 'M87,522 L113,520 C114,536 115,550 114,564 C113,578 108,591 100,596 C93,600 86,597 83,590 C80,580 81,565 84,552 C85,541 86,530 87,522 Z',
			),
			array(
				'id'     => 'thigh-right',
				'label'  => __( 'Right thigh', 'interactive-body-map' ),
				'group'  => 'leg-right',
				'mirror' => false,
				'd'      => 'M114,545 C118,580 120,620 122,658 C124,688 126,704 128,714 C145,720 163,720 180,714 C180,694 181,672 183,648 C186,612 191,570 196,528 C190,536 183,542 176,545 Z',
			),
			array(
				'id'     => 'knee-right',
				'label'  => __( 'Right knee', 'interactive-body-map' ),
				'group'  => 'leg-right',
				'mirror' => false,
				'd'      => 'M128,714 C145,720 163,720 180,714 C180,732 179,748 178,758 C163,763 145,763 130,758 C129,744 128,728 128,714 Z',
			),
			array(
				'id'     => 'lower-leg-right',
				'label'  => __( 'Right lower leg', 'interactive-body-map' ),
				'group'  => 'leg-right',
				'mirror' => false,
				'd'      => 'M130,758 C145,763 163,763 178,758 C179,782 177,808 174,834 C171,862 170,900 172,932 L146,932 C146,900 141,862 135,834 C129,808 126,782 130,758 Z',
			),
			array(
				'id'     => 'foot-right',
				'label'  => __( 'Right foot', 'interactive-body-map' ),
				'group'  => 'leg-right',
				'mirror' => false,
				'd'      => 'M146,932 L172,932 C173,945 175,955 177,963 C180,971 177,977 169,977 L133,977 C125,977 122,971 126,963 C134,951 142,942 146,932 Z',
			),
			array(
				'id'     => 'shoulder-left',
				'label'  => __( 'Left shoulder', 'interactive-body-map' ),
				'group'  => 'arm-left',
				'mirror' => true,
				'd'      => 'M142,206 C122,208 105,215 95,232 C85,250 77,272 77,296 C77,306 77,312 78,320 L118,322 C119,310 121,300 124,292 C127,262 133,232 142,206 Z',
			),
			array(
				'id'     => 'upper-arm-left',
				'label'  => __( 'Left upper arm', 'interactive-body-map' ),
				'group'  => 'arm-left',
				'mirror' => true,
				'd'      => 'M78,320 L118,322 C118,346 118,368 117,386 C117,396 117,402 117,408 L81,410 C80,400 79,390 79,378 C78,358 78,340 78,320 Z',
			),
			array(
				'id'     => 'forearm-left',
				'label'  => __( 'Left forearm', 'interactive-body-map' ),
				'group'  => 'arm-left',
				'mirror' => true,
				'd'      => 'M81,410 L117,408 C117,432 116,456 114,478 C113,496 113,508 113,520 L87,522 C85,508 83,494 82,476 C79,452 77,431 81,410 Z',
			),
			array(
				'id'     => 'hand-left',
				'label'  => __( 'Left hand', 'interactive-body-map' ),
				'group'  => 'arm-left',
				'mirror' => true,
				'd'      => 'M87,522 L113,520 C114,536 115,550 114,564 C113,578 108,591 100,596 C93,600 86,597 83,590 C80,580 81,565 84,552 C85,541 86,530 87,522 Z',
			),
			array(
				'id'     => 'thigh-left',
				'label'  => __( 'Left thigh', 'interactive-body-map' ),
				'group'  => 'leg-left',
				'mirror' => true,
				'd'      => 'M114,545 C118,580 120,620 122,658 C124,688 126,704 128,714 C145,720 163,720 180,714 C180,694 181,672 183,648 C186,612 191,570 196,528 C190,536 183,542 176,545 Z',
			),
			array(
				'id'     => 'knee-left',
				'label'  => __( 'Left knee', 'interactive-body-map' ),
				'group'  => 'leg-left',
				'mirror' => true,
				'd'      => 'M128,714 C145,720 163,720 180,714 C180,732 179,748 178,758 C163,763 145,763 130,758 C129,744 128,728 128,714 Z',
			),
			array(
				'id'     => 'lower-leg-left',
				'label'  => __( 'Left lower leg', 'interactive-body-map' ),
				'group'  => 'leg-left',
				'mirror' => true,
				'd'      => 'M130,758 C145,763 163,763 178,758 C179,782 177,808 174,834 C171,862 170,900 172,932 L146,932 C146,900 141,862 135,834 C129,808 126,782 130,758 Z',
			),
			array(
				'id'     => 'foot-left',
				'label'  => __( 'Left foot', 'interactive-body-map' ),
				'group'  => 'leg-left',
				'mirror' => true,
				'd'      => 'M146,932 L172,932 C173,945 175,955 177,963 C180,971 177,977 169,977 L133,977 C125,977 122,971 126,963 C134,951 142,942 146,932 Z',
			),
		);
	}

	/**
	 * Decorative contours drawn once, on the centre line.
	 *
	 * @return string[]
	 */
	public static function details() {
		return array(
			'M157,84 C158,46 177,26 200,26 C223,26 242,46 243,84',
			'M200,188 L200,268',
			'M200,338 L200,424',
			'M176,368 C188,373 212,373 224,368',
			'M178,398 C189,403 211,403 222,398',
			'M196,428 C200,425 204,429 203,434 C201,440 196,439 195,434 Z',
		);
	}

	/**
	 * Decorative contours drawn twice, once mirrored.
	 *
	 * @return string[]
	 */
	public static function details_paired() {
		return array(
			'M154,82 C147,81 145,92 148,101 C150,106 153,107 156,104',
			'M197,182 C187,191 172,195 154,192',
			'M154,198 C161,230 176,250 198,252',
			'M126,238 C113,240 102,250 96,264',
			'M100,340 C104,362 106,382 106,402',
			'M142,486 C156,504 174,517 194,524',
			'M84,550 C92,545 105,545 113,550',
			'M152,782 C155,822 157,872 157,918',
			'M133,964 C143,960 159,959 172,961',
		);
	}

	/**
	 * Display name for each region group.
	 *
	 * @return array<string, string>
	 */
	public static function group_labels() {
		return array(
			'head' => __( 'Head', 'interactive-body-map' ),
			'neck' => __( 'Neck', 'interactive-body-map' ),
			'chest' => __( 'Chest', 'interactive-body-map' ),
			'abdomen' => __( 'Abdomen', 'interactive-body-map' ),
			'pelvis' => __( 'Pelvis', 'interactive-body-map' ),
			'arm-right' => __( 'Right arm', 'interactive-body-map' ),
			'arm-left' => __( 'Left arm', 'interactive-body-map' ),
			'leg-right' => __( 'Right leg', 'interactive-body-map' ),
			'leg-left' => __( 'Left leg', 'interactive-body-map' ),
		);
	}

	/**
	 * Group order, which is also the tab order.
	 *
	 * @return string[]
	 */
	public static function group_order() {
		return array(
			'head',
			'neck',
			'chest',
			'abdomen',
			'pelvis',
			'arm-right',
			'arm-left',
			'leg-right',
			'leg-left',
		);
	}

	/**
	 * Side-less fallback keys, so one setting can cover both limbs.
	 *
	 * @return array<string, string>
	 */
	public static function plurals() {
		return array(
			'arm' => 'arms',
			'leg' => 'legs',
			'hand' => 'hands',
			'foot' => 'feet',
			'knee' => 'knees',
			'thigh' => 'thighs',
			'shoulder' => 'shoulders',
			'forearm' => 'forearms',
			'upper-arm' => 'upper-arms',
			'lower-leg' => 'lower-legs',
		);
	}
}
