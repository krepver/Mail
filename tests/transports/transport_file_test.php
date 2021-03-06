<?php
/**
 *
 * Licensed to the Apache Software Foundation (ASF) under one
 * or more contributor license agreements.  See the NOTICE file
 * distributed with this work for additional information
 * regarding copyright ownership.  The ASF licenses this file
 * to you under the Apache License, Version 2.0 (the
 * "License"); you may not use this file except in compliance
 * with the License.  You may obtain a copy of the License at
 * 
 *   http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied.  See the License for the
 * specific language governing permissions and limitations
 * under the License.
 *
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @version //autogentag//
 * @filesource
 * @package Mail
 * @subpackage Tests
 */

/**
 * @package Mail
 * @subpackage Tests
 */
class ezcMailTransportFileTest extends ezcTestCase
{
    public static function suite()
    {
         return new PHPUnit_Framework_TestSuite( "ezcMailTransportFileTest" );
    }

    public function testSingle()
    {
        $set = new ezcMailFileSet( array( dirname( __FILE__ ) . '/../parser/data/gmail/html_mail.mail' ) );
        $data = '';
        $line = $set->getNextLine();
        while ( $line !== null )
        {
            $data .= $line;
            $line = $set->getNextLine();
        }
        $this->assertEquals( file_get_contents( dirname( __FILE__ ) . '/../parser/data/gmail/html_mail.mail' ),
                             $data );
        $this->assertEquals( false, $set->nextMail() );
    }

    public function testMultiple()
    {
        $set = new ezcMailFileSet( array( dirname( __FILE__ ) . '/../parser/data/gmail/html_mail.mail',
                                          dirname( __FILE__ ) . '/../parser/data/gmail/simple_mail_with_text_subject_and_body.mail' ));
        // check first mail
        $data = '';
        $line = $set->getNextLine();
        while ( $line !== null )
        {
            $data .= $line;
            $line = $set->getNextLine();
        }
        $this->assertEquals( file_get_contents( dirname( __FILE__ ) . '/../parser/data/gmail/html_mail.mail' ),
                             $data );
        // advance to next
        $this->assertEquals( true, $set->nextMail() );

        // check second mail
        $data = '';
        $line = $set->getNextLine();
        while ( $line !== null )
        {
            $data .= $line;
            $line = $set->getNextLine();
        }
        $this->assertEquals( file_get_contents( dirname( __FILE__ ) . '/../parser/data/gmail/simple_mail_with_text_subject_and_body.mail' ),
                             $data );


        $this->assertEquals( false, $set->nextMail() );
    }

    public function testNoSuchFile()
    {
        $set = new ezcMailFileSet( array( 'no_such_file', 'not_this_either' ) );
        $this->assertEquals( null, $set->getNextLine() );
        $this->assertEquals( false, $set->nextMail() );
    }

    public function testStdIn()
    {
        $dataDir = dirname( __FILE__ ) . "/data/";
        $phpPath = isset( $_SERVER["_"] ) ? $_SERVER["_"] : "/bin/env php";
        $scriptFile = "{$dataDir}/parse-script.php";
        $desc = array(
            0 => array( "pipe", "r" ),  // stdin
            1 => array( "pipe", "w" ),  // stdout
            2 => array( "pipe", "w" )   // stderr
        );
        $proc = proc_open("'{$phpPath}' '{$scriptFile}'", $desc, $pipes );

        fwrite( $pipes[0], file_get_contents( dirname( __FILE__ ) . '/../parser/data/gmail/html_mail.mail' ) );
        fclose( $pipes[0] );

        $ret = '';

        while (!feof( $pipes[1] ) )
        {
            $ret .= fgets( $pipes[1] );
        }
        self::assertEquals( "Frederik Holljen <sender@gmail.com>\nGmail: HTML mail\n", $ret );
    }
}
?>
