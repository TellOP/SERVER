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
class StanfordDETagger extends WebServiceClientController {

    public function __construct($appObject) {
        $config = $appObject->getConfig();

        $this->model = $config['stanford-german-tagger']['model'];
        $this->set_model($this->model);

        $this->java_path = $config['stanford-german-tagger']['java_path']; // the command to run java
        $this->java_options = $config['stanford-german-tagger']['java_options']; // array of java switch options
        $this->jar = $config['stanford-german-tagger']['jar']; // the jar to use located in $path
        $this->path = $config['stanford-german-tagger']['path'];	// path to where the standford postagger directory resides

        $this->tmp_path = $config['stanford-german-tagger']['tmp_path'];// directory to store tmp file
        $this->tmp_prefix = $config['stanford-german-tagger']['tmp_prefix']; // prefix of tmp file
        $this->tmp_permission = $config['stanford-german-tagger']['tmp_permission']; // permission to set tmp file

        if(!file_exists($this->get_jar()))
        {
            //echo "Jar not found: " . $this->get_jar() . "\n";
            $this->logger->addInfo("Jar not found: " . $this->get_jar());
            $this->dieWSValidation("Jar not found: " . $this->get_jar());
            return;
        }
        if(!file_exists($this->get_model()))
        {
            //echo "Model not found: ".$this->get_model() . "\n";
            $this->logger->addInfo("Model not found: ".$this->get_model());
            $this->dieWSValidation("Model not found: ".$this->get_model());
            return;
        }

        $this->logger = $appObject->getApplicationLogger();
    }

    /**
     * @var Logger Logger from $appObject. Init by constructor
     */
    private $logger;

    /**
     * @var string PoS tagger model
     */
    private $model;

    /**
     * @var string The command to run java
     */
    private $java_path;

    /**
     * @var array(string) array of java switch options
     */
    private $java_options;

    /**
     * @var string the jar to use located in $path
     */
    private $jar;

    /**
     * @var string path to where the standford postagger directory resides
     */
    private $path;

    /**
     * Temporary files - the text is stored in a tmp file which is parsed
     * @var string directory to store tmp file
     */
    private $tmp_path;

    /**
     * Temporary files - the text is stored in a tmp file which is parsed
     * @var string prefix of tmp file
     */
    private $tmp_prefix;

    /**
     * Temporary files - the text is stored in a tmp file which is parsed
     * @var permission to set tmp file
     */
    private $tmp_permission;

    /**
     * POS Tag seperator such as John_NNP where _ is the seperator
     * @var string used for tagged output
     */
    private $separator = '_';

    /**
     * POS Tag seperator such as John_NNP where _ is the seperator
     * @var string used for better seperation when used in array output
     */
    private $best_separator = '#_#';

    /**
     * @var bool Sanatizing text (Use Pspell for spell checking (if installed)
     */
    private $use_pspell = true;

    /**
     * In Array Tag Options - For us with array_tag() method only
     * @var string Hash types for sentence include 'none', 'md5', 'base64', 'sha1' (http://us3.php.net/manual/en/function.hash.php)
     */
    private $hash_type = 'md5';

    /**
     * In Array Tag Options - For us with array_tag() method only
     * @var string so "John_NNP" "Smith_NNP" becomes "John Smith_NNP"
     */
    private $merge_proper_nouns = true;

    /**
     * In Array Tag Options - For us with array_tag() method only
     * @var string so "one hundred and thirty" or "two and a half" is grouped as a single CD
     */
    private $merge_cardinal_numbers = true;

    /**
     * In Array Tag Options - For us with array_tag() method only
     * @var string numbers the order of each tag occurance
     */
    private $sequence_tags = true;

    /**
     * In Array Tag Options - For us with array_tag() method only
     * @var string adds new field in array records that masks with a * specified tag types in list below.
     */
    private $tag_mask_types = true;

    /**
     * In Array Tag Options - For us with array_tag() method only
     * @var array(string) TagMask list
     */
    private $tag_mask_list = array(
        //'#',		// Pound sign
        //'$',		// Dollar sign
        //'"',		// Close double quote
        //'``',		// Open double quote
        //"'",		// Close single quote
        //'`',		// Open single quote
        //',',		// Comma
        '.',		// Final punctuation
        //':',		// Colon or semi-colon
        //'-LRB-',// Left bracket
        //'-RBR-',// Right bracket
        //'CC',		// Coordinating conjunction : and, but, or, yet, for, nor, so
        'CD',			// Cardinal number 1, 2, 3, one, two, three hundred

        //'DT',			// Determiner :
        //'EX',			// Existential there : There is a cult of ignorance in the United States.
        //'FW',			// Foreign word :
        //'IN',			// Preposition : links nouns, pronouns and phrases to other words in a sentence. on, beneath, against, beside, over, during

        'JJ',			// adjective : sweet, angry, bright, cold, long : also orignal numbers like "3rd" fastest, "6th" place
        'JJR',		// comparitive adjective : sweeter, angrier, brighter, colder, longer
        'JJS',		// superlative adjective : sweetest, angriest, brightest, coldest, longest

        //'LS',			// List item marker :
        //'MD',			// Modal : can, may, must, should, would

        'NN', 		// singular noun : girl, mother, nurse, city, town, bicycle, doll, train, dream, truth, pride, colony, team, litter, covey
        'NNS',	 	// plural noun : children, men, girls, mothers, nurses, cities, towns, bikes, dolls, trains, dreams, colonies, teams, litters
        'NNP',		// proper singular noun : John, Smith, Pizza Hut
        'NNPS',		// proper plural noun: Kennedys

        //'PDT',	// predeterminer : all, both, half
        //'POS',	// possessive ending : 's, s'
        //'PRP',	// personal pronoun : I, me, myself, we us, ourselves, you, yourself (http://en.wikipedia.org/wiki/English_personal_pronouns)
        //'PP?',	// possessive pronouns : her, your, his, hers, my, their, yours, whose, one's, theirs, its, our (http://examples.yourdictionary.com/examples-of-possessive-pronouns.html)
        'RB',		// adverb : slowly, now, soon, suddenly (http://en.wikipedia.org/wiki/Adverb)
        'RBR',		// comparative adverb : more quietly, more carefully, more happily, harder, faster, earlier
        'RBS',		// superlative adverb : most quiely, most carefully, most happily, hardest, fastest, earliest
        'RP',		// particle : prepositions that modify a verb instead of a noun. along, away, back, by, down, forward, in, off, on, out, over, round, under, up
        //'SYM',	// symbol :
        //'TO',		// to
        'UH',		// injection : ah, oh, brrr, oops, huh?, booh, eh, mwahaha, bwahaha, yay, yuck, yeah (http://www.vidarholen.net/contents/interjections/)

        'VB',		// verb, base form : walk, skip, jump
        'VBD',		// verb, past tense : walked, shipped, jumped
        'VBG',		// verb, gerund/present participle : walking, skipping, jumping
        'VBN',		// verb, past participle : have walked, have skipped, have jumped
        'VBP',		// verb, non 3rd person: sing, present :
        'VBZ',		// verb, 3rd person: sing, present :
        //'WDT',		// wh-determiner : what, which, whose, whatever, whichever
        //'WP',		// wh-pronoun : what, which, where, when, who, whom, whose. (And maybe: whether.)
        //'WP$',		// possesive wh-pronoun : whose
        //'WRB',		// wh-adverb : how, where, when
        //' '			// blank space
    );

    /**
     * Performs a query to the Stanford Tagger for the German language.
     * @param \TellOP\Application $appObject Application object.
     * @return void
     */
    public function displayPage($appObject) {
        // Perform validation
        if (!isset($_GET['q'])) {
            $this->dieWSValidation('The q parameter is missing.');
        }

        $text = base64_decode($_GET['q']);
        //echo $text . "\n";
        if (!$text) {
            $this->dieWSValidation('The q parameter must be base64 encoded.');
        }
        //echo "<xmp>";
        //var_dump($text);
        //echo "</xmp>";
        $this->logger->addInfo("Pos Text: " . $text);

        $tags = $this->array_tag($text);
        //echo "<hr color='red'><xmp>";
        //var_dump($tags);
        //echo "</xmp><hr color='red'>";
        $out = array();
        foreach ($tags as $rootTag) {
            //echo "<hr color='blue'><xmp>";
            //var_dump($rootTag);
            //echo "</xmp><hr color='blue'>";
            //continue;
            foreach ($rootTag["tagged"] as $word) {
                //echo "<hr color='green'><xmp>";
                //var_dump($word);
                //echo "</xmp><hr color='green'>";
                //continue;
                // http://nlp.stanford.edu/software/spanish-faq.shtml
                switch ($word['tag']) {
                    case 'ADJA':
                    case 'ADJD':
                        array_push($out, array("pos" => 'adjective', "token" => $word['token']));
                        break;

                    case 'KOUI':
                    case 'KOUS':
                    case 'KON':
                    case 'KOKOM':
                        array_push($out, array("pos" => 'conjunction', "token" => $word['token']));
                        break;

                    case 'ITJ':
                        array_push($out, array("pos" => 'interjectionOrDiscourseMarker', "token" => $word['token']));
                        break;

                    case 'NN':
                        array_push($out, array("pos" => 'commonNoun', "token" => $word['token']));
                        break;

                    case 'NE':
                        array_push($out, array("pos" => 'properNoun', "token" => $word['token']));
                        break;

                    case 'FM':
                        array_push($out, array("pos" => 'foreignWord', "token" => $word['token']));
                        break;

                    case 'PDS':
                    case 'PDAT':
                    case 'PIS':
                    case 'PIAT':
                    case 'PIDAT':
                    case 'PPER':
                    case 'PPOSS':
                    case 'PPOSAT':
                    case 'PRELS':
                    case 'PRELAT':
                    case 'PRF':
                    case 'PWS':
                    case 'PWAT':
                        array_push($out, array("pos" => 'pronoun', "token" => $word['token']));
                        break;

                    case 'ADV':
                    case 'PWAV':
                    case 'PAV':
                        array_push($out, array("pos" => 'adverb', "token" => $word['token']));
                        break;

                    case 'APPR':
                    case 'APPRART':
                    case 'APPO':
                    case 'APZR':
                        array_push($out, array("pos" => 'preposition', "token" => $word['token']));
                        break;

                    case 'PTKNEG':
                    case 'PTKVZ':
                    case 'PTKANT':
                    case 'PTKA':
                        array_push($out, array("pos" => 'existentialParticle', "token" => $word['token']));
                        break;

                    case 'VVFIN':
                    case 'VVIMP':
                    case 'VVINF':
                    case 'VVIZU':
                    case 'VVPP':
                    case 'VAFIN':
                    case 'VAIMP':
                    case 'VAINF':
                    case 'VAPP':
                    case 'VMFIN':
                    case 'VMINF':
                    case 'VMPP':
                        array_push($out, array("pos" => 'verb', "token" => $word['token']));
                        break;

                    case 'ORD':
                        array_push($out, array("pos" => 'ordinal', "token" => $word['token']));
                        break;

                    case '$,':
                    case '$.':
                    case '$(':
                        // DO NOTHING WITH PUNCTUATION
                        break;

                    default:
                        array_push($out, array("pos" => 'unclassified', "token" => $word['token'] . ' (May be ' . $word['tag'] . ')'));
                        break;
                }
            }
        }
        echo json_encode($out);
        $this->logger->addInfo("Pos decode: " . json_encode($out));
        //echo "</xmp>";
    }

    public function set_path($path)
    {
        $this->path = trim(rtrim(trim($path),'/')).'/';
    }

    public function merge_proper_nounds($val = true)
    {
        $this->merge_proper_nouns = $val;
    }

    public function sequence_tags($val = true)
    {
        $this->sequence_tags = $val;
    }

    public function tag_mask_types($val = true)
    {
        $this->tag_mask_types = $val;
    }

    public function tag_mask_list($taglist = array())
    {
        $this->tag_mask_types_list= $taglist;
    }

    public function set_hash($val = '')
    {
        if($val == '') $val = 'none';

        $this->hash_type = $val;
    }


    public function set_stanford_path($path)
    {
        $this->path = trim(rtrim($path,'/'));
    }

    public function set_model($model)
    {
        $this->model = trim(ltrim($model));
    }

    public function get_model()
    {
        return rtrim($this->path,'/').'/models/'.ltrim($this->model,'/');
    }

    public function get_jar()
    {
        return rtrim($this->path,'/').'/'.ltrim($this->jar,'/');
    }

    public function set_jar($jar)
    {
        $this->jar = trim(ltrim($jar));
    }

    public function set_java_path($java_path)
    {
        $this->java_path = trim($java_path);
    }

    public function set_java_options($java_options = array())
    {
        $this->java_options = $java_options;
    }

    public function set_tmp_path($path)
    {
        $this->tmp_path = trim(rtrim($path,'/'));
    }

    public function set_tmp_prefix($prefix)
    {
        $this->tmp_prefix = trim(ltrim($prefix,'/'));
    }

    public function set_tmp_permission($perm)
    {
        $this->tmp_permission = $perm;
    }

    public function set_tag_separator($separator = '_')
    {
        $this->separator = trim($separator);
    }

    public function get_tag_separator()
    {
        return $this->separator;
    }

    public function tag($txt, $normalize = true, $separator = '')
    {
        error_reporting(E_ALL);
        //echo "TAG\n";

        if($separator == '')
        {
            $separator = $this->separator;
        }

        $tf = tempnam($this->tmp_path, $this->tmp_prefix);
        //echo "Tempnam: " . $tf . "\n";
        chmod($tf, octdec($this->tmp_permission));

        chmod($tf, 0644);

        $words = explode(' ',$txt);

        //echo "words: " . json_encode($words) . "\n";

        if($this->use_pspell)
        {
            $txt = $this->spellcheck($txt);
        }
        
        //echo "txt: " . $txt . "\n";

        file_put_contents($tf, $txt);

        $options = implode(' ', $this->java_options);
        $model = $this->path.'/'.$this->model;

        $descriptorspec = array(
            0 => array("pipe", "r"),  // stdin
            1 => array("pipe", "w"),  // stdout
            2 => array("pipe", "w")   // stderr
        );

        //$cmd = escapeshellcmd('java '.$options.' -cp "'.$this->jar.';" edu.stanford.nlp.tagger.maxent.MaxentTagger -model '.$this->get_model().' -textFile '.$tf.' -outputFormat slashTags -tagSeparator '.$separator.' -encoding utf8');
        $cmd = escapeshellcmd('java '.$options.' -cp '.$this->get_jar().' edu.stanford.nlp.tagger.maxent.MaxentTagger -model '.$this->get_model().' -textFile '.$tf.' -outputFormat slashTags -tagSeparator '.$separator.' -encoding utf8');

        //echo "cmd: " . $cmd . "\n";

        $process = proc_open($cmd, $descriptorspec, $pipes, dirname($this->get_jar()));

        $output = null;
        $errors = null;
        if(is_resource($process))
        {
            // ignore stdin - input
            fclose($pipes[0]);

            // get stdout - output
            $output = stream_get_contents($pipes[1]);
            fclose($pipes[1]);

            // get stderr - errors
            $errors = stream_get_contents($pipes[2]);
            fclose($pipes[2]);

            // prevent deadlock by closing pipe before calling proc_close
            $return_value = proc_close($process);
            if($return_value == -1)
            {
                global $logger;
                $logger->addError("Java process error: " . $cmd);
                return;
            }
        }

        unlink($tf);

        return $output;
    }

    public function array_tag($txt,$normalize = true)
    {
        return $this->tagged_to_array($this->tag($txt,$normalize,$this->best_separator),$this->best_separator);
    }

    public function tagged_to_array($tagged, $separator)
    {
        error_reporting(E_ALL ^ E_NOTICE);
        $arr = array();

        if(!$tagged) return $arr;

        if($separator == '')
        {
            $separator = $this->separator;
        }

        $sentences = explode("\n", $tagged);
        foreach($sentences as $k => $v)
        {
            $sequence = array();
            if(trim($v) == '')
            {
                continue;
            }
            $tagrec = array();
            $tags = explode(' ', trim($v));
            $last_tag = 'START';
            $i = 0;
            foreach($tags as $kk => $vv)
            {
                $parts = explode($separator, trim($vv));
                $tag = array();

                // start - merge proper nouns
                if($this->merge_proper_nouns)
                {
                    if(($parts[1] == 'NNP') || ($parts[1] == 'NNPS'))
                    {
                        if(($last_tag == 'NNP') || ($last_tag == 'NNPS'))
                        {
                            $tagrec[$i - 1]['token'] .= ' '.$parts[0]; // append this word to last token
                            $tagrec[$i - 1]['tag'] = $parts[1]; // the final proper noun type is used
                            continue;
                        }
                    }
                }

                // end - merge proper nouns

                // start - merge cardinal numbers
                if($this->merge_cardinal_numbers)
                {
                    if($parts[1] == 'CD')
                    {
                        if($last_tag == 'CD')
                        {
                            $tagrec[$i - 1]['token'] .= ' '.$parts[0]; // append this word to last token
                            continue;
                        }
                    }
                }

                // end - merge cardinal numbers

                $last_tag = $parts[1];

                $tag['token'] = $parts[0];
                $tag['tag'] = $parts[1];

                // start - sequence tags
                if($this->sequence_tags)
                {
                    if($sequence[$parts[1]] > 0)
                    {
                        $sequence[$parts[1]]++;
                    }
                    else
                    {
                        $sequence[$parts[1]] = 1;
                    }
                    $tag['seq'] = $sequence[$parts[1]];
                }
                // end sequence proper nouns

                // start - tag masking
                if($this->tag_mask_types)
                {
                    if(in_array($parts[1],$this->tag_mask_list))
                    {
                        $tag['mask'] = '*';
                    }
                    else
                    {
                        $tag['mask'] = $parts[0];
                    }

                }
                // end - tag masking

                $tagrec[] = $tag;
                $i++;

            }

            $tagdata = array();
            $tagdata['tagged'] = $tagrec;

            $tagdata['sentence'] = '';
            $tagdata['tag_set'] = '';
            $tagdata['mask_set'] = '';
            foreach($tagrec as $k => $v)
            {
                // sentence
                if($tagdata['sentence'] != '') $tagdata['sentence'] .= ' ';
                $tagdata['sentence'] .= $v['token'];

                // tag set
                if($tagdata['tag_set'] != '') $tagdata['tag_set'] .= ' ';
                if($this->sequence_tags)
                {
                    $tagdata['tag_set'] .= '{'.$v['tag'].'-'.$v['seq'].'}';
                }
                else
                {
                    $tagdata['tag_set'] .= '{'.$v['tag'].'}';
                }

                // mask set
                if($tagdata['mask_set'] != '') $tagdata['mask_set'] .= ' ';
                if($v['mask'] == '*')
                {
                    if($this->sequence_tags)
                    {
                        $tagdata['mask_set'] .= '{'.$v['tag'].'-'.$v['seq'].'}';
                    }
                    else
                    {
                        $tagdata['mask_set'] .= '{'.$v['tag'].'}';
                    }
                }
                else
                {
                    $tagdata['mask_set'] .= $v['mask'];
                }
            }

            // generate hashes
            if($this->hash_type == 'md5')
            {
                $tagdata['hash_sentence'] = md5($tagdata['sentence']);
                $tagdata['hash_tag_set'] = md5($tagdata['tag_set']);
                $tagdata['hash_mask_set'] = md5($tagdata['mask_set']);
            }
            else if($this->hash_type == 'base64')
            {
                $tagdata['hash_sentence'] = base64_encode($tagdata['sentence']);
                $tagdata['hash_tag_set'] = base64_encode($tagdata['tag_set']);
                $tagdata['hash_mask_set'] = base64_encode($tagdata['mask_set']);
            }
            else if($this->hash_type == 'sha1')
            {
                $tagdata['hash_sentence'] = sha1($tagdata['sentence']);
                $tagdata['hash_tag_set'] = sha1($tagdata['tag_set']);
                $tagdata['hash_mask_set'] = sha1($tagdata['mask_set']);
            }

            $arr[] = $tagdata; // add seqntence array to output array
        }

        return $arr;
    }

    public function spellcheck($txt)
    {
        $o = '';
        if(function_exists('pspell_new'))
        {
            $pspell_link = pspell_new("en");
            $words = null;
            foreach($words as $k => $v)
            {
                if (!pspell_check($pspell_link, $v))
                {
                    $o .= pspell_suggest($pspell_link, $v).' ';
                }
            }
            $txt = $o;
        }
        return $txt;
    }
}
