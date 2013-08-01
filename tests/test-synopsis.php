<?php

use WP_CLI\SynopsisParser;

class SynopsisParserTest extends PHPUnit_Framework_TestCase {

	function testEmpty() {
		$r = SynopsisParser::parse( ' ' );

		$this->assertEmpty( $r );
	}

	function testPositional() {
		$r = SynopsisParser::parse( '<foo> [<bar>]' );

		$this->assertCount( 2, $r );

		$param = $r[0];
		$this->assertEquals( 'positional', $param['type'] );
		$this->assertFalse( $param['optional'] );

		$param = $r[1];
		$this->assertEquals( 'positional', $param['type'] );
		$this->assertTrue( $param['optional'] );
	}

	function testFlag() {
		$r = SynopsisParser::parse( '[--foo]' );

		$this->assertCount( 1, $r );

		$param = $r[0];
		$this->assertEquals( 'flag', $param['type'] );
		$this->assertTrue( $param['optional'] );

		// flags can't be mandatory
		$r = SynopsisParser::parse( '--foo' );

		$this->assertCount( 1, $r );

		$param = $r[0];
		$this->assertEquals( 'unknown', $param['type'] );
	}

	function testGeneric() {
		$r = SynopsisParser::parse( '--<field>=<value> [--<field>=<value>] --<field>[=<value>] [--<field>[=<value>]]' );

		$this->assertCount( 4, $r );

		$param = $r[0];
		$this->assertEquals( 'generic', $param['type'] );
		$this->assertFalse( $param['optional'] );

		$param = $r[1];
		$this->assertEquals( 'generic', $param['type'] );
		$this->assertTrue( $param['optional'] );

		$param = $r[2];
		$this->assertEquals( 'unknown', $param['type'] );

		$param = $r[3];
		$this->assertEquals( 'unknown', $param['type'] );
	}

	function testAssoc() {
		$r = SynopsisParser::parse( '--foo=<value> [--bar=<value>] [--bar[=<value>]] --bar[=<value>]' );

		$this->assertCount( 4, $r );

		$param = $r[0];
		$this->assertEquals( 'assoc', $param['type'] );
		$this->assertFalse( $param['optional'] );

		$param = $r[1];
		$this->assertEquals( 'assoc', $param['type'] );
		$this->assertTrue( $param['optional'] );

		$param = $r[2];
		$this->assertEquals( 'assoc', $param['type'] );
		$this->assertTrue( $param['optional'] );
		$this->assertTrue( $param['value']['optional'] );

		$param = $r[3];
		$this->assertEquals( 'assoc', $param['type'] );
		$this->assertFalse( $param['optional'] );
		$this->assertTrue( $param['value']['optional'] );
	}

	function testInvalidAssoc() {
		$r = SynopsisParser::parse( '--bar=[<value>] --count=100' );

		$this->assertCount( 2, $r );

		$this->assertEquals( 'unknown', $r[0]['type'] );
		$this->assertEquals( 'unknown', $r[1]['type'] );
	}

	function testRepeating() {
		$r = SynopsisParser::parse( '<positional>... [--<field>=<value>...]' );

		$this->assertCount( 2, $r );

		$param = $r[0];
		$this->assertEquals( 'positional', $param['type'] );
		$this->assertTrue( $param['repeating'] );

		$param = $r[1];
		$this->assertEquals( 'generic', $param['type'] );
		$this->assertTrue( $param['repeating'] );
	}

	function testCombined() {
		$r = SynopsisParser::parse( '<positional> --assoc=<someval> --<field>=<value> [--flag]' );

		$this->assertCount( 4, $r );

		$this->assertEquals( 'positional', $r[0]['type'] );
		$this->assertEquals( 'assoc', $r[1]['type'] );
		$this->assertEquals( 'generic', $r[2]['type'] );
		$this->assertEquals( 'flag', $r[3]['type'] );
	}

	function testAllowedValueCharacters() {
		$r = SynopsisParser::parse( '--capitals=<VALUE> --hyphen=<val-ue> --combined=<VAL-ue> --disallowed=<wrong:char>' );

		$this->assertCount( 4, $r );

		$param = $r[0];
		$this->assertEquals( 'assoc', $param['type'] );
		$this->assertFalse( $param['optional'] );

		$param = $r[1];
		$this->assertEquals( 'assoc', $param['type'] );
		$this->assertFalse( $param['optional'] );

		$param = $r[2];
		$this->assertEquals( 'assoc', $param['type'] );
		$this->assertFalse( $param['optional'] );

		$this->assertEquals( 'unknown', $r[3]['type'] );
	}
}