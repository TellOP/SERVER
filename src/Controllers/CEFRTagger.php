<?php
/* Copyright © 2016 University of Murcia
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * Adapted by Mattia Zago (www.zagomattia.it).
 * Original License:
 * PHP Class Stanford POS Tagger 1.1.0 - PHP Wrapper for Stanford's Part of Speech Java Tagger
 * Copyright (C) 2014 Charles R Hays http://www.charleshays.com
 *
 *
 * @version 1.1.0 (2/4/2014)
 *		1.0.0 - release
 *		1.1.0 - added merge cardinal numbers
 *
 *  This library is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU Lesser General Public
 *  License as published by the Free Software Foundation; either
 *  version 2.1 of the License, or (at your option) any later version.
 *
 *  This library is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 *  Lesser General Public License for more details.
 *
 *  You should have received a copy of the GNU Lesser General Public
 *  License along with this library; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 */
namespace TellOP\Controllers;
class CEFRTagger extends WebServiceClientController {

    public function __construct($appObject) {

        $this->logger = $appObject->getApplicationLogger();
    }

    /**
     * Performs a query to the Stanford Tagger for the German language.
     * @param \TellOP\Application $appObject Application object.
     * @return void
     */
    public function displayPage($appObject) {
        $apppdo = $appObject->getApplicationPDO();
        //echo "<xmp>";
        // Perform validation
        if (!isset($_GET['q'])) {
            $this->dieWSValidation('The q parameter is missing.');
        }

        $text = base64_decode($_GET['q']);
        //echo $text . "\n";
        if (!$text) {
            $this->dieWSValidation('The q parameter must be base64 encoded.');
        }

        $words = array();
        preg_match_all("/\w+['-]?\w?+/", $text, $matches, PREG_SET_ORDER, 0);
        foreach ($matches as $tmp) {
            $word = strtolower($tmp[0]);
            $word = str_replace("something'dn't've", "something had not have", $word);
            $word = str_replace("somebody'dn't've", "somebody had not have", $word);
            $word = str_replace("someone'dn't've", "someone had not have", $word);
            $word = str_replace("something'dn't", "something had not", $word);
            $word = str_replace("something'd've", "something would have", $word);
            $word = str_replace("y'all'll'ven't", "you all will have not", $word);
            $word = str_replace("somebody'dn't", "somebody had not", $word);
            $word = str_replace("somebody'd've", "somebody would have", $word);
            $word = str_replace("there'dn't've", "there would not have", $word);
            $word = str_replace("they'lln't've", "they will not have", $word);
            $word = str_replace("they'll'ven't", "they will have not", $word);
            $word = str_replace("y'all'dn't've", "you all would not have", $word);
            $word = str_replace("shouldn't've", "should not have", $word);
            $word = str_replace("someone'dn't", "someone had not", $word);
            $word = str_replace("someone'd've", "someone would have", $word);
            $word = str_replace("something'll", "something shall", $word);
            $word = str_replace("they'dn't've", "they would not have", $word);
            $word = str_replace("they'd'ven't", "they would have not", $word);
            $word = str_replace("couldn't've", "could not have", $word);
            $word = str_replace("mightn't've", "might not have", $word);
            $word = str_replace("oughtn't've", "ought not to have", $word);
            $word = str_replace("she'dn't've", "she had not have", $word);
            $word = str_replace("somebody'll", "somebody shall", $word);
            $word = str_replace("something'd", "something had", $word);
            $word = str_replace("something's", "something has", $word);
            $word = str_replace("we'lln't've", "we will not have", $word);
            $word = str_replace("wouldn't've", "would not have", $word);
            $word = str_replace("y'all'll've", "you all will have", $word);
            $word = str_replace("he'dn't've", "he had not have", $word);
            $word = str_replace("it'dn't've", "it had not have",$word);
            $word = str_replace("mustn't've", "must not have", $word);
            $word = str_replace("somebody'd", "somebody had", $word);
            $word = str_replace("somebody's", "somebody has", $word);
            $word = str_replace("someone'll", "someone shall", $word);
            $word = str_replace("there'dn't", "there had not", $word);
            $word = str_replace("there'd've", "there would have", $word);
            $word = str_replace("they'ven't", "they have not", $word);
            $word = str_replace("we'dn't've", "we had not have", $word);
            $word = str_replace("y'all'd've", "you all would have", $word);
            $word = str_replace("y'all'on't", "you all will not", $word);
            $word = str_replace("hadn't've", "had not have", $word);
            $word = str_replace("i'dn't've", "i had not have", $word);
            $word = str_replace("should've", "should have", $word);
            $word = str_replace("shouldn't", "should not", $word);
            $word = str_replace("someone'd", "someone had", $word);
            $word = str_replace("someone's", "someone has", $word);
            $word = str_replace("they'dn't", "they had not", $word);
            $word = str_replace("they'd've", "they would have", $word);
            $word = str_replace("you'ren't", "you are not", $word);
            $word = str_replace("you'ven't", "you have not", $word);
            $word = str_replace("could've", "could have", $word);
            $word = str_replace("couldn't", "could not", $word);
            $word = str_replace("mightn't", "might not", $word);
            $word = str_replace("might've", "might have", $word);
            $word = str_replace("oughtn't", "ought not", $word);
            $word = str_replace("she'dn't", "she had not", $word);
            $word = str_replace("she'd've", "she would have", $word);
            $word = str_replace("she'sn't", "she has not", $word);
            $word = str_replace("there're", "there are", $word);
            $word = str_replace("where've", "where have", $word);
            $word = str_replace("who'd've", "who would have", $word);
            $word = str_replace("won't've", "will not have", $word);
            $word = str_replace("would've", "would have", $word);
            $word = str_replace("wouldn't", "would not", $word);
            $word = str_replace("y'all'll", "you all will", $word);
            $word = str_replace("y'all're", "you all are", $word);
            $word = str_replace("you'd've", "you would have", $word);
            $word = str_replace("doesn't", "does not", $word);
            $word = str_replace("haven't", "have not", $word);
            $word = str_replace("he'dn't", "he had not", $word);
            $word = str_replace("he'd've", "he would have", $word);
            $word = str_replace("he'sn't", "he has not ", $word);
            $word = str_replace("i'ven't", "i have not", $word);
            $word = str_replace("it'dn't", "it had not ", $word);
            $word = str_replace("it'd've", "it would have", $word);
            $word = str_replace("it'sn't", "it has not", $word);
            $word = str_replace("mustn't", "must not", $word);
            $word = str_replace("must've", "must have", $word);
            $word = str_replace("needn't", "need not", $word);
            $word = str_replace("o'clock", "of the clock", $word);
            $word = str_replace("that'll", "that will", $word);
            $word = str_replace("there'd", "there would", $word);
            $word = str_replace("there's", "there has", $word);
            $word = str_replace("they'll", "they will", $word);
            $word = str_replace("they're", "they are", $word);
            $word = str_replace("they've", "they have", $word);
            $word = str_replace("we'd've", "we would have", $word);
            $word = str_replace("we'dn't", "we would not", $word);
            $word = str_replace("weren't", "were not", $word);
            $word = str_replace("what'll", "what will", $word);
            $word = str_replace("what're", "what are", $word);
            $word = str_replace("what've", "what have", $word);
            $word = str_replace("where'd", "where did", $word);
            $word = str_replace("where's", "where has", $word);
            $word = str_replace("aren't", "are not", $word);
            $word = str_replace("didn't", "did not", $word);
            $word = str_replace("hadn't", "had not", $word);
            $word = str_replace("hasn't", "has not", $word);
            $word = str_replace("how'll", "how will", $word);
            $word = str_replace("i'dn't", "i had not", $word);
            $word = str_replace("i'd've", "i would have", $word);
            $word = str_replace("not've", "not have", $word);
            $word = str_replace("shan't", "shall not", $word);
            $word = str_replace("she'll", "she shall", $word);
            $word = str_replace("s'pose", "suppose", $word);
            $word = str_replace("that's", "that has", $word);
            $word = str_replace("that'd", "that would", $word);
            $word = str_replace("they'd", "they had", $word);
            $word = str_replace("wasn't", "was not", $word);
            $word = str_replace("what'd", "what did", $word);
            $word = str_replace("what's", "what has", $word);
            $word = str_replace("when's", "when has", $word);
            $word = str_replace("who'll", "who shall", $word);
            $word = str_replace("who're", "who are", $word);
            $word = str_replace("who've", "who have", $word);
            $word = str_replace("why'll", "why will", $word);
            $word = str_replace("why're", "why are", $word);
            $word = str_replace("you'll", "you shall", $word);
            $word = str_replace("you're", "you are", $word);
            $word = str_replace("you've", "you have", $word);
            $word = str_replace("ain't", "am not", $word);
            $word = str_replace("amn't", "am not", $word);
            $word = str_replace("can't", "cannot", $word);
            $word = str_replace("cap'm", "captain", $word);
            $word = str_replace("cap'n", "captain", $word);
            $word = str_replace("don't", "do not", $word);
            $word = str_replace("he'll", "he shall", $word);
            $word = str_replace("how'd", "how did", $word);
            $word = str_replace("how's", "how is", $word);
            $word = str_replace("isn't", "is not", $word);
            $word = str_replace("it'll", "it shall", $word);
            $word = str_replace("let's", "let us", $word);
            $word = str_replace("ma'am", "madam", $word);
            $word = str_replace("ne'er", "never", $word);
            $word = str_replace("she'd", "she had", $word);
            $word = str_replace("she's", "she has", $word);
            $word = str_replace("'twas", "it was", $word);
            $word = str_replace("we'll", "we will", $word);
            $word = str_replace("we're", "we are", $word);
            $word = str_replace("we've", "we have", $word);
            $word = str_replace("who'd", "who would", $word);
            $word = str_replace("who's", "who has", $word);
            $word = str_replace("why'd", "why did", $word);
            $word = str_replace("why's", "why has ", $word);
            $word = str_replace("won't", "will not", $word);
            $word = str_replace("ya'll", "you all", $word);
            $word = str_replace("y'all", "you all", $word);
            $word = str_replace("you'd", "you had", $word);
            $word = str_replace("e'er", "ever", $word);
            $word = str_replace("he'd", "he had", $word);
            $word = str_replace("he's", "he has", $word);
            $word = str_replace("i'll", "i will", $word);
            $word = str_replace("i've", "i have", $word);
            $word = str_replace("it'd", "it had", $word);
            $word = str_replace("it's", "it is", $word);
            $word = str_replace("'sup", "what is up", $word);
            $word = str_replace("'tis", "it is", $word);
            $word = str_replace("we'd", "we had", $word);
            $word = str_replace("i'd", "i had", $word);
            $word = str_replace("i'm", "i am", $word);
            $word = str_replace("ol'", "old", $word);
            preg_match_all("/\w+['-]?\w?+/", $word, $res, PREG_SET_ORDER, 0);
            foreach($res as $x) {
                array_push($words, $x[0]);
            }
        };

        $out = array();
        foreach ($words as $lemma) {
            $getBaseLemma = $apppdo->prepare("select distinct `base` from `word_analysis_lemmas` where `lemma` = :l");

            if (!$getBaseLemma->execute(array(":l" => $lemma))) {
                //throw new DatabaseException('Unable to execute the query');
                $result_word = $lemma;
            }
            else if ($getBaseLemma->rowCount() != 1) {
                $result_word = $lemma;
            }
            else
            {
                if (($actFields = $getBaseLemma->fetch(\PDO::FETCH_ASSOC)) === FALSE) {
                    //throw new DatabaseException('Unable to fetch the results');
                    $result_word = $lemma;
                }
                else {
                    $result_word = $actFields["base"];
                }
            }
            $getBaseLemma->closeCursor();

            $getCEFR = $apppdo->prepare("select distinct `word`, `cefr_level`, `part_of_speech` from `word_analysis_tokens` where `word` = :w group by `word` order by `cefr_level`");

            if (!$getCEFR->execute(array(":w" => $result_word))) {
                //throw new DatabaseException('Unable to execute the query');
                $cefr = 6; //UNKNOWN
                $pos =  20; // UNCLASSIFIED
            }
            else if ($getCEFR->rowCount() != 1) {
                $cefr = 6; //UNKNOWN
                $pos =  20; // UNCLASSIFIED
            }
            else
            {
                if (($actFields = $getCEFR->fetch(\PDO::FETCH_ASSOC)) === FALSE) {
                    //throw new DatabaseException('Unable to fetch the results');
                    $cefr = 6; //UNKNOWN
                    $pos =  20; // UNCLASSIFIED
                }
                else {
                    $cefr = $actFields["cefr_level"];
                    $pos = $actFields["part_of_speech"];
                }
            }
            $getCEFR->closeCursor();

            switch ($pos) {
            case  0: $pos = "adjective"; break;
            case  1: $pos = "adverb"; break;
            case  2: $pos = "clauseOpener"; break;
            case  3: $pos = "conjunction"; break;
            case  4: $pos = "determiner"; break;
            case  5: $pos = "determinerPronoun"; break;
            case  6: $pos = "existentialParticle"; break;
            case  7: $pos = "foreignWord"; break;
            case  8: $pos = "genitive"; break;
            case  9: $pos = "infinitiveMarker"; break;
            case 10: $pos = "interjectionOrDiscourseMarker"; break;
            case 11: $pos = "letterAsWord"; break;
            case 12: $pos = "negativeMarker"; break;
            case 13: $pos = "commonNoun"; break;
            case 14: $pos = "properNoun"; break;
            case 15: $pos = "partOfProperNoun"; break;
            case 16: $pos = "cardinalNumber"; break;
            case 17: $pos = "ordinal"; break;
            case 18: $pos = "preposition"; break;
            case 19: $pos = "pronoun"; break;
            case 20:
            default: $pos = "unclassified"; break;
            case 21: $pos = "verb"; break;
            case 22: $pos = "modalVerb"; break;
            case 23: $pos = "auxiliaryVerb"; break;
            case 24: $pos = "exclamation"; break;
            }
            
            array_push($out, array( "word" => $result_word, "cefr" => "".$cefr, "pos" => $pos));
        }

        //echo "<hr color='blue'><xmp>";
        //var_dump($out);
        //echo "</xmp><hr color='blue'>";
        //die();

        echo json_encode($out);
        $this->logger->addInfo("CEFR decode: " . json_encode($out));
    }
}
