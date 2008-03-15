<?php
/**
 * AES Encryption Class. This class supports three lenghts of key (128, 192, 256)
 * @author Marcin F. Wiœniowski <marcin.wisniowski@mfw.pl>
 * @version 1.0.2
 * @licence GPLv3
 * Based on Federal Information Processing Standards Publication 197 - 26th November 2001
 */

class AES {
/** @var int Number of rounds in AES algorithm (Nr)  */
	private $number_of_rounds;
/** @var int Number of columns (32-bit words) comprising the State. (Nb) */
	private $block_size;
/** @var int Number of 32-bit words comprising the Cipher Key  (Nk) */
	private $key_size;
/** @var array S-Box: Non-linear substitution table used in several byte substitution transformations */
	private $s_box;
/** @var array Inverted S-Box */
	private $s_box_inverted;
/** @var array Araay of [pow(2,x),{00},{00},{00}] values used as round constant value */
	private $round_constants;
/** @var array Logaritmic array used in Galois filed multiplication proccess */
	private $log;
/** @var array Expotencial array used in Galois filed multiplication proccess */
	private $exp;
/** @var array Array of collumn (words) */
	private $state;
	
	const AES128		= 0;
	const AES192		= 1;
	const AES256		= 2;
	
	const WORD_LENGTH 	= 4;
	const BYTE_LENGTH	= 8;
/**
 * Class constructor default construkts AES128 Objeckt
 * @param int Key strength default values is AES128. Also supports AES192 and AES256 values.
 */
	public function __construct($strength=self::AES128) {
		switch($strength) {
			case self::AES256:
				$this->key_size = 8;
				$this->block_size = 4;
				$this->number_of_rounds = 14;
				break;
			case self::AES192:
				$this->key_size = 6;
				$this->block_size = 4;
				$this->number_of_rounds = 12;
				break;
			case self::AES128:
			default:
				$this->key_size = 4;
				$this->block_size = 4;
				$this->number_of_rounds = 10;
				break;
		}
		$this->createSBox();
		$this->createInvertedSBox();
		$this->createRoundConstants();
		$this->createLogarithmicArray();
		$this->createExpotecialArray();
		$this->state = array();
	}
/**
 * Class destructor
 */ 
	public function __destruct() {}
/**
 * Definition of method which takes undefined methods of this Class
 */ 
	public function __call($name, $params) {
		throw new Exception("Undefined method call of AES class. Method name: ".$name);
	}
/**
 * Creates S-Box Table
 */
	private function createSBox() {
		$this->s_box = array(
		0x63, 0x7C, 0x77, 0x7B, 0xF2, 0x6B, 0x6F, 0xC5, 0x30, 0x01, 0x67, 0x2B, 0xFE, 0xD7, 0xAB, 0x76,
		0xCA, 0x82, 0xC9, 0x7D, 0xFA, 0x59, 0x47, 0xF0, 0xAD, 0xD4, 0xA2, 0xAF, 0x9C, 0xA4, 0x72, 0xC0,
		0xB7, 0xFD, 0x93, 0x26, 0x36, 0x3F, 0xF7, 0xCC, 0x34, 0xA5, 0xE5, 0xF1, 0x71, 0xD8, 0x31, 0x15,
		0x04, 0xC7, 0x23, 0xC3, 0x18, 0x96, 0x05, 0x9A, 0x07, 0x12, 0x80, 0xE2, 0xEB, 0x27, 0xB2, 0x75,
		0x09, 0x83, 0x2C, 0x1A, 0x1B, 0x6E, 0x5A, 0xA0, 0x52, 0x3B, 0xD6, 0xB3, 0x29, 0xE3, 0x2F, 0x84,
		0x53, 0xD1, 0x00, 0xED, 0x20, 0xFC, 0xB1, 0x5B, 0x6A, 0xCB, 0xBE, 0x39, 0x4A, 0x4C, 0x58, 0xCF,
		0xD0, 0xEF, 0xAA, 0xFB, 0x43, 0x4D, 0x33, 0x85, 0x45, 0xF9, 0x02, 0x7F, 0x50, 0x3C, 0x9F, 0xA8,
		0x51, 0xA3, 0x40, 0x8F, 0x92, 0x9D, 0x38, 0xF5, 0xBC, 0xB6, 0xDA, 0x21, 0x10, 0xFF, 0xF3, 0xD2,
		0xCD, 0x0C, 0x13, 0xEC, 0x5F, 0x97, 0x44, 0x17, 0xC4, 0xA7, 0x7E, 0x3D, 0x64, 0x5D, 0x19, 0x73,
		0x60, 0x81, 0x4F, 0xDC, 0x22, 0x2A, 0x90, 0x88, 0x46, 0xEE, 0xB8, 0x14, 0xDE, 0x5E, 0x0B, 0xDB,
		0xE0, 0x32, 0x3A, 0x0A, 0x49, 0x06, 0x24, 0x5C, 0xC2, 0xD3, 0xAC, 0x62, 0x91, 0x95, 0xE4, 0x79,
		0xE7, 0xC8, 0x37, 0x6D, 0x8D, 0xD5, 0x4E, 0xA9, 0x6C, 0x56, 0xF4, 0xEA, 0x65, 0x7A, 0xAE, 0x08,
		0xBA, 0x78, 0x25, 0x2E, 0x1C, 0xA6, 0xB4, 0xC6, 0xE8, 0xDD, 0x74, 0x1F, 0x4B, 0xBD, 0x8B, 0x8A,
		0x70, 0x3E, 0xB5, 0x66, 0x48, 0x03, 0xF6, 0x0E, 0x61, 0x35, 0x57, 0xB9, 0x86, 0xC1, 0x1D, 0x9E,
		0xE1, 0xF8, 0x98, 0x11, 0x69, 0xD9, 0x8E, 0x94, 0x9B, 0x1E, 0x87, 0xE9, 0xCE, 0x55, 0x28, 0xDF,
		0x8C, 0xA1, 0x89, 0x0D, 0xBF, 0xE6, 0x42, 0x68, 0x41, 0x99, 0x2D, 0x0F, 0xB0, 0x54, 0xBB, 0x16);
	}
/**
 * Creates invertet S-Box Table
 */
	private function createInvertedSBox() {
		$this->s_box_inverted = array(
		0x52, 0x09, 0x6A, 0xD5, 0x30, 0x36, 0xA5, 0x38, 0xBF, 0x40, 0xA3, 0x9E, 0x81, 0xF3, 0xD7, 0xFB,
		0x7C, 0xE3, 0x39, 0x82, 0x9B, 0x2F, 0xFF, 0x87, 0x34, 0x8E, 0x43, 0x44, 0xC4, 0xDE, 0xE9, 0xCB,
		0x54, 0x7B, 0x94, 0x32, 0xA6, 0xC2, 0x23, 0x3D, 0xEE, 0x4C, 0x95, 0x0B, 0x42, 0xFA, 0xC3, 0x4E,
		0x08, 0x2E, 0xA1, 0x66, 0x28, 0xD9, 0x24, 0xB2, 0x76, 0x5B, 0xA2, 0x49, 0x6D, 0x8B, 0xD1, 0x25,
		0x72, 0xF8, 0xF6, 0x64, 0x86, 0x68, 0x98, 0x16, 0xD4, 0xA4, 0x5C, 0xCC, 0x5D, 0x65, 0xB6, 0x92,
		0x6C, 0x70, 0x48, 0x50, 0xFD, 0xED, 0xB9, 0xDA, 0x5E, 0x15, 0x46, 0x57, 0xA7, 0x8D, 0x9D, 0x84,
		0x90, 0xD8, 0xAB, 0x00, 0x8C, 0xBC, 0xD3, 0x0A, 0xF7, 0xE4, 0x58, 0x05, 0xB8, 0xB3, 0x45, 0x06,
		0xD0, 0x2C, 0x1E, 0x8F, 0xCA, 0x3F, 0x0F, 0x02, 0xC1, 0xAF, 0xBD, 0x03, 0x01, 0x13, 0x8A, 0x6B,
		0x3A, 0x91, 0x11, 0x41, 0x4F, 0x67, 0xDC, 0xEA, 0x97, 0xF2, 0xCF, 0xCE, 0xF0, 0xB4, 0xE6, 0x73,
		0x96, 0xAC, 0x74, 0x22, 0xE7, 0xAD, 0x35, 0x85, 0xE2, 0xF9, 0x37, 0xE8, 0x1C, 0x75, 0xDF, 0x6E,
		0x47, 0xF1, 0x1A, 0x71, 0x1D, 0x29, 0xC5, 0x89, 0x6F, 0xB7, 0x62, 0x0E, 0xAA, 0x18, 0xBE, 0x1B,
		0xFC, 0x56, 0x3E, 0x4B, 0xC6, 0xD2, 0x79, 0x20, 0x9A, 0xDB, 0xC0, 0xFE, 0x78, 0xCD, 0x5A, 0xF4,
		0x1F, 0xDD, 0xA8, 0x33, 0x88, 0x07, 0xC7, 0x31, 0xB1, 0x12, 0x10, 0x59, 0x27, 0x80, 0xEC, 0x5F,
		0x60, 0x51, 0x7F, 0xA9, 0x19, 0xB5, 0x4A, 0x0D, 0x2D, 0xE5, 0x7A, 0x9F, 0x93, 0xC9, 0x9C, 0xEF,
		0xA0, 0xE0, 0x3B, 0x4D, 0xAE, 0x2A, 0xF5, 0xB0, 0xC8, 0xEB, 0xBB, 0x3C, 0x83, 0x53, 0x99, 0x61,
		0x17, 0x2B, 0x04, 0x7E, 0xBA, 0x77, 0xD6, 0x26, 0xE1, 0x69, 0x14, 0x63, 0x55, 0x21, 0x0C, 0x7D);
	}
/**
 * Creates Round Contstants Table: Array of [pow(2,x),{00},{00},{00}] values used as round constant value 
 */
	private function createRoundConstants() {
		$this->round_constants = array(
		0x01000000, 0x02000000, 0x04000000, 0x08000000, 0x10000000, 0x20000000, 0x40000000, 0x80000000, 
		0x1B000000, 0x36000000, 0x6C000000, 0xD8000000, 0xAB000000, 0x4D000000, 0x9A000000, 0x2F000000,
		0x5E000000, 0xBC000000, 0x63000000, 0xC6000000, 0x97000000, 0x35000000, 0x6A000000, 0xD4000000,
		0xB3000000, 0x7D000000, 0xFA000000, 0xEF000000, 0xC5000000, 0x91000000);
	}
/**
 * Creates Logarithmic array with generator number 0xe5
 */
	private function createLogarithmicArray() {
		$this->log = array(
		0x00, 0xFF, 0xC8, 0x08, 0x91, 0x10, 0xD0, 0x36, 0x5A, 0x3E, 0xD8, 0x43, 0x99, 0x77, 0xFE, 0x18, 
		0x23, 0x20, 0x07, 0x70, 0xA1, 0x6C, 0x0C, 0x7F, 0x62, 0x8B, 0x40, 0x46, 0xC7, 0x4B, 0xE0, 0x0E, 
		0xEB, 0x16, 0xE8, 0xAD, 0xCF, 0xCD, 0x39, 0x53, 0x6A, 0x27, 0x35, 0x93, 0xD4, 0x4E, 0x48, 0xC3, 
		0x2B, 0x79, 0x54, 0x28, 0x09, 0x78, 0x0F, 0x21, 0x90, 0x87, 0x14, 0x2A, 0xA9, 0x9C, 0xD6, 0x74, 
		0xB4, 0x7C, 0xDE, 0xED, 0xB1, 0x86, 0x76, 0xA4, 0x98, 0xE2, 0x96, 0x8F, 0x02, 0x32, 0x1C, 0xC1, 
		0x33, 0xEE, 0xEF, 0x81, 0xFD, 0x30, 0x5C, 0x13, 0x9D, 0x29, 0x17, 0xC4, 0x11, 0x44, 0x8C, 0x80, 
		0xF3, 0x73, 0x42, 0x1E, 0x1D, 0xB5, 0xF0, 0x12, 0xD1, 0x5B, 0x41, 0xA2, 0xD7, 0x2C, 0xE9, 0xD5, 
		0x59, 0xCB, 0x50, 0xA8, 0xDC, 0xFC, 0xF2, 0x56, 0x72, 0xA6, 0x65, 0x2F, 0x9F, 0x9B, 0x3D, 0xBA, 
		0x7D, 0xC2, 0x45, 0x82, 0xA7, 0x57, 0xB6, 0xA3, 0x7A, 0x75, 0x4F, 0xAE, 0x3F, 0x37, 0x6D, 0x47, 
		0x61, 0xBE, 0xAB, 0xD3, 0x5F, 0xB0, 0x58, 0xAF, 0xCA, 0x5E, 0xFA, 0x85, 0xE4, 0x4D, 0x8A, 0x05, 
		0xFB, 0x60, 0xB7, 0x7B, 0xB8, 0x26, 0x4A, 0x67, 0xC6, 0x1A, 0xF8, 0x69, 0x25, 0xB3, 0xDB, 0xBD, 
		0x66, 0xDD, 0xF1, 0xD2, 0xDF, 0x03, 0x8D, 0x34, 0xD9, 0x92, 0x0D, 0x63, 0x55, 0xAA, 0x49, 0xEC, 
		0xBC, 0x95, 0x3C, 0x84, 0x0B, 0xF5, 0xE6, 0xE7, 0xE5, 0xAC, 0x7E, 0x6E, 0xB9, 0xF9, 0xDA, 0x8E, 
		0x9A, 0xC9, 0x24, 0xE1, 0x0A, 0x15, 0x6B, 0x3A, 0xA0, 0x51, 0xF4, 0xEA, 0xB2, 0x97, 0x9E, 0x5D, 
		0x22, 0x88, 0x94, 0xCE, 0x19, 0x01, 0x71, 0x4C, 0xA5, 0xE3, 0xC5, 0x31, 0xBB, 0xCC, 0x1F, 0x2D, 
		0x3B, 0x52, 0x6F, 0xF6, 0x2E, 0x89, 0xF7, 0xC0, 0x68, 0x1B, 0x64, 0x04, 0x06, 0xBF, 0x83, 0x38);
	}
/**
 * Creates Expotencial array with generator number 0xe5
 */	
	private function createExpotecialArray() {
		$this->exp = array(
		0x01, 0xe5, 0x4c, 0xb5, 0xfb, 0x9f, 0xfc, 0x12, 0x03, 0x34, 0xd4, 0xc4, 0x16, 0xba, 0x1f, 0x36, 
		0x05, 0x5c, 0x67, 0x57, 0x3a, 0xd5, 0x21, 0x5a, 0x0f, 0xe4, 0xa9, 0xf9, 0x4e, 0x64, 0x63, 0xee, 
		0x11, 0x37, 0xe0, 0x10, 0xd2, 0xac, 0xa5, 0x29, 0x33, 0x59, 0x3b, 0x30, 0x6d, 0xef, 0xf4, 0x7b, 
		0x55, 0xeb, 0x4d, 0x50, 0xb7, 0x2a, 0x07, 0x8d, 0xff, 0x26, 0xd7, 0xf0, 0xc2, 0x7e, 0x09, 0x8c, 
		0x1a, 0x6a, 0x62, 0x0b, 0x5d, 0x82, 0x1b, 0x8f, 0x2e, 0xbe, 0xa6, 0x1d, 0xe7, 0x9d, 0x2d, 0x8a, 
		0x72, 0xd9, 0xf1, 0x27, 0x32, 0xbc, 0x77, 0x85, 0x96, 0x70, 0x08, 0x69, 0x56, 0xdf, 0x99, 0x94, 
		0xa1, 0x90, 0x18, 0xbb, 0xfa, 0x7a, 0xb0, 0xa7, 0xf8, 0xab, 0x28, 0xd6, 0x15, 0x8e, 0xcb, 0xf2, 
		0x13, 0xe6, 0x78, 0x61, 0x3f, 0x89, 0x46, 0x0d, 0x35, 0x31, 0x88, 0xa3, 0x41, 0x80, 0xca, 0x17, 
		0x5f, 0x53, 0x83, 0xfe, 0xc3, 0x9b, 0x45, 0x39, 0xe1, 0xf5, 0x9e, 0x19, 0x5e, 0xb6, 0xcf, 0x4b, 
		0x38, 0x04, 0xb9, 0x2b, 0xe2, 0xc1, 0x4a, 0xdd, 0x48, 0x0c, 0xd0, 0x7d, 0x3d, 0x58, 0xde, 0x7c, 
		0xd8, 0x14, 0x6b, 0x87, 0x47, 0xe8, 0x79, 0x84, 0x73, 0x3c, 0xbd, 0x92, 0xc9, 0x23, 0x8b, 0x97, 
		0x95, 0x44, 0xdc, 0xad, 0x40, 0x65, 0x86, 0xa2, 0xa4, 0xcc, 0x7f, 0xec, 0xc0, 0xaf, 0x91, 0xfd, 
		0xf7, 0x4f, 0x81, 0x2f, 0x5b, 0xea, 0xa8, 0x1c, 0x02, 0xd1, 0x98, 0x71, 0xed, 0x25, 0xe3, 0x24, 
		0x06, 0x68, 0xb3, 0x93, 0x2c, 0x6f, 0x3e, 0x6c, 0x0a, 0xb8, 0xce, 0xae, 0x74, 0xb1, 0x42, 0xb4, 
		0x1e, 0xd3, 0x49, 0xe9, 0x9c, 0xc8, 0xc6, 0xc7, 0x22, 0x6e, 0xdb, 0x20, 0xbf, 0x43, 0x51, 0x52, 
		0x66, 0xb2, 0x76, 0x60, 0xda, 0xc5, 0xf3, 0xf6, 0xaa, 0xcd, 0x9a, 0xa0, 0x75, 0x54, 0x0e, 0x01);
	}
/**
 * Encrypts 16 bytes of data using AES algoritm
 * @param string Decrypted Content
 * @return string Encrypted Content
 */	
	public function encrypt($content, $key) {
		$expand_key = $this->keyExpansion($this->convertToWords($key));
		$this->state = $this->convertToWords($content);
		$this->addRoundKey($this->createRoundKey(0, $expand_key));
		for ($round = 1; $round < $this->number_of_rounds; $round++) {
			$this->subBytes();
			$this->shiftRows();
			$this->mixColumns();
			$this->addRoundKey($this->createRoundKey($round, $expand_key));
		}
		$this->subBytes();
		$this->shiftRows();
		$this->addRoundKey($this->createRoundKey($this->number_of_rounds, $expand_key));
		return $this->convertToHexString();
	}
/**
 * Decrypts encrypteta 16 bytes of data using AES algoritm
 * @param string Encrypted Content
 * @return string Decrypted Content
 */
	public function decrypt($content, $key) {
		$expand_key = $this->keyExpansion($this->convertToWords($key));
		$this->state = $this->convertToWords($content);
		$this->addRoundKey($this->createRoundKey($this->number_of_rounds, $expand_key));
		for ($round = $this->number_of_rounds - 1; $round > 0; $round--) {
			$this->invShiftRows();
			$this->invSubBytes();
			$this->addRoundKey($this->createRoundKey($round, $expand_key));
			$this->invMixColumns();
		}
		$this->invShiftRows();
		$this->invSubBytes();
		$this->addRoundKey($this->createRoundKey(0, $expand_key));
		return $this->convertToHexString();
	}
/**
 * Processes the State table using a nonlinear byte substitution table (S-box).
 */
	private function subBytes() {
		for ($i=0; $i < $this->block_size; $i++) {
			$this->state[$i] = $this->subWord($this->state[$i]);
		}
	}
/**
 * Inversion of subBytes() method
 * @see subBytes()
 */
	private function invSubBytes() {
		for ($i=0; $i < $this->block_size; $i++) {
			$this->state[$i] = $this->subWord($this->state[$i], true);
		}
	}
/**
 * Processes the State by cyclically shifting the last three rows of the State by different offsets.
 */
	private function shiftRows() {
		for ($i=1; $i < self::WORD_LENGTH; $i++) {
			$buffer = array();
			for ($j=$this->block_size-1; $j >= 0; $j--) {
				$pos = ($i + $j) % $this->block_size;
				$buffer[$j] = $this->getByteFromWord($this->state[$j], $i);
				$byte = isset($buffer[$pos]) ? $buffer[$pos] : $this->getByteFromWord($this->state[$pos], $i);
				$this->state[$j] = $this->putByteIntoWord( $byte, $this->state[$j], $i);
			}
		}
	}
/**
 * Inversion of shiftRows() method.
 * @see shiftRows()
 */
	private function invShiftRows() {
		for ($i=1; $i < self::WORD_LENGTH; $i++) {
			$buffer = array();
			for ($j=0; $j < $this->block_size; $j++) {
				$pos = ($i + $j) % $this->block_size;
				$buffer[$pos] = $this->getByteFromWord($this->state[$pos], $i);
				$byte = isset($buffer[$j]) ? $buffer[$j] : $this->getByteFromWord($this->state[$j], $i);
				$this->state[$pos] = $this->putByteIntoWord( $byte, $this->state[$pos], $i);
			}
		}
	}
/**
 * Takes all of the columns of theState and mixes their data to produce new columns.
 */
	private function mixColumns() {
		$mul1 = array();
		$mul2 = array();
		$mul3 = array();
		for ($i=0; $i < $this->block_size; $i++) {
			for ($j = 0; $j < self::WORD_LENGTH; $j++) {
				$mul1[$j] = $this->getByteFromWord($this->state[$i], $j);
				$mul2[$j] = $this->galoisFieldMultiplication($mul1[$j], 0x02);
				$mul3[$j] = $mul2[$j] ^ $mul1[$j];
			}
			for ($j = 0; $j < self::WORD_LENGTH; $j++) {
				$byte = $mul2[$j] ^ $mul1[($j+3) % $this->block_size] ^ $mul1[($j+2) % $this->block_size] ^ $mul3[($j+1) % $this->block_size];
				$this->state[$i] = $this->putByteIntoWord( $byte, $this->state[$i], $j);
			}
		}
	}
/**
 * Inversion of mixColumns()
 * @see mixColumns()
 */
	private function invMixColumns() {
		$mulE = array();
		$mulD = array();
		$mulB = array();
		$mul9 = array();
		for ($i=0; $i < $this->block_size; $i++) {
			for ($j = 0; $j < self::WORD_LENGTH; $j++) {
				$temp = $this->getByteFromWord($this->state[$i], $j);
				$mulE[$j] = $this->galoisFieldMultiplication($temp, 0x0E);
				$mulD[$j] = $this->galoisFieldMultiplication($temp, 0x0D);
				$mulB[$j] = $this->galoisFieldMultiplication($temp, 0x0B);
				$mul9[$j] = $this->galoisFieldMultiplication($temp, 0x09);
			}
			for ($j = 0; $j < self::WORD_LENGTH; $j++) {
				$byte = $mulE[$j] ^ $mul9[($j+3) % $this->block_size] ^ $mulD[($j+2) % $this->block_size] ^ $mulB[($j+1) % $this->block_size];
				$this->state[$i] = $this->putByteIntoWord( $byte, $this->state[$i], $j);
			}
		}
	}
/**
 * Adds Round Key to the State using an XOR operation.
 */
	private function addRoundKey($key) {
		for ($i=0; $i < $this->block_size; $i++) {
			$this->state[$i] ^= $key[$i];
		}
	}
/**
 * Generates Key schedule from Cipher Key
 * @return array Expanded key array
 */
	private function keyExpansion($key) {
		$expanded_key = array();
		for ($i=0; $i<$this->key_size; $i++) {
			$expanded_key[$i] = $key[$i];
		}
		for ($i=$this->key_size; $i<$this->block_size * ($this->number_of_rounds + 1); $i++) {
			$temp = $expanded_key[$i - 1];
			if ($i % $this->key_size == 0)
				$temp = $this->subWord($this->rotWord($temp)) ^ $this->round_constants[$i / $this->key_size -1];
			else if ($this->key_size > 6 && $i % $this->key_size == 4)
				$temp = $this->subWord($temp);
			$expanded_key[$i] = $expanded_key[$i - $this->key_size] ^ $temp;
		}
		return $expanded_key;
	}
/**
 * Creates array of 4 words from expanded key depend of round
 * @param int Number of round
 * @param array Expanded key array
 * @return array Round key.
 */
	private function createRoundKey($round, $key) {
		return array($key[self::WORD_LENGTH * $round], $key[self::WORD_LENGTH * $round + 1], $key[self::WORD_LENGTH * $round + 2], $key[self::WORD_LENGTH * $round + 3]);
	}
/**
 * Galios field multiplication function
 * @param int First byte
 * @param int Second byte
 * @return int Multiplication result
 */
	private function galoisFieldMultiplication($number_a, $number_b) {
		$temp = $this->exp[($this->log[$number_a] + $this->log[$number_b]) % 0xFF];
		$result = $number_a ? ($number_b ? $temp : 0) : 0;		
		return $result;
		/* // Old Implementanion
		$result = 0; 
		for ($i=0; $i<self::BYTE_LENGTH; $i++) {
			if ($number_b & 1) $result ^= $number_a;
			$number_a = ($number_a << 1) ^ (($number_a & 0x80) ? 0x1B : 0);
			$number_b >>= 1;
		}
		return $result; */
	}
/**
 * Changing value of each byte in word using SBOX table
 * @param word Input word
 * @param bool Inversion flag. Default value: false
 * @return word Result word of transformation
 */
	private function subWord($word, $invers=false) {
		if (!$invers)
			return 	$this->s_box[(( $word >> 24 ) & 0x000000FF)] << 24 |
					$this->s_box[(($word & 0x00FF0000) >> 16)] << 16 |
					$this->s_box[(($word & 0x0000FF00) >> 8 )] << 8 |
					$this->s_box[($word & 0x000000FF)];
		else
			return 	$this->s_box_inverted[(( $word >> 24 ) & 0x000000FF)] << 24 |
					$this->s_box_inverted[(($word & 0x00FF0000) >> 16)] << 16 |
					$this->s_box_inverted[(($word & 0x0000FF00) >> 8 )] << 8 |
					$this->s_box_inverted[($word & 0x000000FF)];
		
	}
/**
 * Clock-wise rotation of bytes in word [aa bb cc dd] -> [bb cc dd aa]
 * @param word Input word
 * @return word Result of transformation
 */
	private function rotWord($word) {
		return (( $word << 8 ) & 0xFFFFFFFF ) | (( $word >> 24 ) & 0x000000FF);
	}
/**
 * Get specyfic byte from word
 * @param word Input word
 * @param int Position of byte in word
 * @return byte Selected byte
 */
	private function getByteFromWord($word, $position) {
		switch ($position) {
			case 0:
				return ( $word >> 24 ) & 0x000000FF;
			case 1:
				return ($word & 0x00FF0000) >> 16;
			case 2:
				return ($word & 0x0000FF00) >> 8;
			case 3:
				return $word & 0x000000FF;
		}
	}
/**
 * Puts byte into word 
 * @param byte Byte
 * @param word Word
 * @param int Position of insertion
 * @return word Modified word
 */
	private function putByteIntoWord($byte, $word, $position) {
		switch ($position) {
			case 0:
				return (($word & 0x00FFFFFF) | (($byte << 24) & 0xFF000000));
			case 1:
				return (($word & 0xFF00FFFF) | (($byte << 16) & 0x00FF0000));
			case 2:
				return (($word & 0xFFFF00FF) | (($byte << 8) & 0x0000FF00));
			case 3:
				return (($word & 0xFFFFFF00) | $byte);
		}
	}
/**
 * Creates words array from hexadecimal string
 * @param string hexadecimal string
 * @return array words reprezentatnion of content
 */
	private function convertToWords($content) {
		$words = array();
		for ($i = 0; $i < strlen($content); $i+=2*$this->block_size){
			$words[$i/(2*$this->block_size)] = hexdec(substr($content, $i, 2*$this->block_size));
		}
		return $words;
	}
/**
 * Converts state array into hexadecimal string
 * @return string 16 bytes string representation of state array
 */
	private function convertToHexString() {
		$string = "";
		for ($i = 0; $i < $this->block_size; $i++) {
			$string .= $this->addZeros(dechex($this->state[$i]));
		}
		return $string;
	}
/**
 * Converts Strings to HEX representation
 * @param string Input string
 * @return string Hex representation
 */
	public function stringToHex($str) {
		$hex="";
		$zeros = "";
		$len = 2 * self::WORD_LENGTH * $this->block_size;
		for ($i = 0; $i < strlen($str); $i++){
			$val = dechex(ord($str{$i}));	    
			if( strlen($val)< 2 ) $val="0".$val;
			$hex.=$val;
		}
		for ($i = 0; $i < $len - strlen($hex); $i++){
			$zeros .= '0';
		}
		return $hex.$zeros;
	}
/**
 * Converts HEX values into strings
 * @param string HEX value in string repreentation
 * @return string 
 */ 
	public function hexToString($hex) {
		$str="";
		for($i=0; $i<strlen($hex); $i=$i+2 ) {
			$temp = hexdec(substr($hex, $i, 2));
			if (!$temp) continue;
			$str .= chr($temp);
		}
		return $str;
	}
/**
 * Add zeros in front od Hex string representation of single word
 * @return string Zeros string
 */
	private function addZeros($word) {
		$len = 2*self::WORD_LENGTH - strlen($word);
		$zeros = "";
		for ($i=0; $i < $len; $i++)
			$zeros .= '0';
		return $zeros.$word;
	}
/**
 * Self testing method
 */
	public function selfTest() {
		$content = '00112233445566778899aabbccddeeff';
		$password = 'Test password';
		$key = md5($password);
		print('Challenge content: 0x'.$content.'<br />');
		print('Password: "'.$password.'", key: 0x'.$key.'<br />');
		
		print('<br />Encryption process<br />');
		$start = microtime(true);
		$this->state = $this->convertToWords($content);
		$time1 = microtime(true);
		$expand_key = $this->keyExpansion($this->convertToWords($key));
		$time2 = microtime(true);
		print('Key expansion time: '.(($time2 - $time1)*1000).' ms<br />');
		$this->addRoundKey($this->createRoundKey(0, $expand_key));
		
		$time1 = microtime(true);
		$this->subBytes();
		$time2 = microtime(true);
		print('Sub bytes time: '.(($time2 - $time1)*1000).' ms<br />');
		
		$time1 = microtime(true);
		$this->shiftRows();
		$time2 = microtime(true);
		print('Shift rows time: '.(($time2 - $time1)*1000).' ms<br />');
		
		$time1 = microtime(true);
		$this->mixColumns();
		$time2 = microtime(true);
		print('Mix columns time: '.(($time2 - $time1)*1000).' ms<br />');
		
		$time1 = microtime(true);
		$this->addRoundKey($this->createRoundKey(1, $expand_key));
		$time2 = microtime(true);
		print('Add round key time: '.(($time2 - $time1)*1000).' ms<br />');
		for ($round = 2; $round < $this->number_of_rounds; $round++) {
			$this->subBytes();
			$this->shiftRows();
			$this->mixColumns();
			$this->addRoundKey($this->createRoundKey($round, $expand_key));
		}
		$this->subBytes();
		$this->shiftRows();
		$this->addRoundKey($this->createRoundKey($this->number_of_rounds, $expand_key));
		$end = microtime(true);
		$content = $this->convertToHexString();
		print('Encryption time: '.(($end-$start)*1000).' ms<br />Encrypted content: 0x'.$content.'<br />');
		
		print('<br />Decryption process<br />');
		
		$start = microtime(true);
		$this->state = $this->convertToWords($content);
		$time1 = microtime(true);
		$expand_key = $this->keyExpansion($this->convertToWords($key));
		$time2 = microtime(true);
		print('Key expansion time: '.(($time2 - $time1)*1000).' ms<br />');
		$this->addRoundKey($this->createRoundKey($this->number_of_rounds, $expand_key));
		
		$time1 = microtime(true);
		$this->invShiftRows();
		$time2 = microtime(true);
		print('Invers shift rows time: '.(($time2 - $time1)*1000).' ms<br />');
		
		$time1 = microtime(true);
		$this->invSubBytes();
		$time2 = microtime(true);
		print('Invers sub bytes time: '.(($time2 - $time1)*1000).' ms<br />');
		
		$time1 = microtime(true);
		$this->addRoundKey($this->createRoundKey($this->number_of_rounds -1, $expand_key));
		$time2 = microtime(true);
		print('Add round key time: '.(($time2 - $time1)*1000).' ms<br />');
		
		$time1 = microtime(true);
		$this->invMixColumns();
		$time2 = microtime(true);
		print('Invers mix columns time: '.(($time2 - $time1)*1000).' ms<br />');

		for ($round = $this->number_of_rounds - 2; $round > 0; $round--) {
			$this->invShiftRows();
			$this->invSubBytes();
			$this->addRoundKey($this->createRoundKey($round, $expand_key));
			$this->invMixColumns();
		}
		$this->invShiftRows();
		$this->invSubBytes();
		$this->addRoundKey($this->createRoundKey(0, $expand_key));
		$end = microtime(true);
		$content = $this->convertToHexString();
		print('Decryption time: '.(($end-$start)*1000).' ms<br />Decrypted content: 0x'.$content.'<br />');
		
	}

}
?>
