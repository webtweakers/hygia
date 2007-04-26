<?php

/**
 * Common reusable uility class.
 * @author Bas van Gaalen
 * @since 1.0
 */
abstract class Utils {

    /**
     * Get file extension (without dot).
     * @param $sFilename string
     * @return string file extension
     * @since 1.0
     */
    public static function getFileExt($sFilename) {
        return substr(strrchr($sFilename, '.'), 1);
    }

    /**
     * Strip trailing slash from file name/path.
     * @param $sFilename string Path
     * @return string Stripped path
     * @since 1.0
     */
    public static function stripTrailingSlash($sFilename) {
        return (substr($sFilename, -1, 1) == '/') ? substr($sFilename, 0, -1) : $sFilename; 
    }

    /**
     * Return singular or plural version of a word,
     * depending on number and including the number.
     * Should move to a Language class in case of multi language project.
     * @param $iNum int The number.
     * @param $sSingular string The singular version of the word.
     * @param $sPlural string The plural version of the word.
     * @return string Something like: "1 man" or "5 men".
     * @since 1.0
     */
    public static function getNoun($iNum, $sSingular, $sPlural) {
        return $iNum . ' ' . (($iNum == 1) ? $sSingular : $sPlural);
    }

    /**
     * This function will remove HTML tags, javascript sections and white space.
     * Also some common HTML entities will be converted to their text equivalent.
     * @param $sContent string Text to be stripped.
     * @return string Plain-text version of input.
     * @since 1.0
     */
    public static function stripHtml($sContent) {
        $aSearch = array('@<script[^>]*?>.*?</script>@si', // Strip out javascript
                         '@<[\/\!]*?[^<>]*?>@si',          // Strip out HTML tags
//                         '@([\r\n])[\s]+@',                // Strip out white space
                         '@&(quot|#34);@i',                // Replace HTML entities
                         '@&(amp|#38);@i',
                         '@&(lt|#60);@i',
                         '@&(gt|#62);@i',
                         '@&(nbsp|#160);@i',
                         '@&(iexcl|#161);@i',
                         '@&(cent|#162);@i',
                         '@&(pound|#163);@i',
                         '@&(copy|#169);@i',
                         '@&#(\d+);@e');                    // evaluate as php

        $aReplace = array('',
                          '',
//                          '\1',
                          '"',
                          '&',
                          '<',
                          '>',
                          ' ',
                          chr(161),
                          chr(162),
                          chr(163),
                          chr(169),
                          'chr(\1)');

        return preg_replace($aSearch, $aReplace, $sContent);
    }

    /**
     * This function returns the percentage of binary characters in the content.
     * (TODO: does this include UTF-8?)
     * @param $sContent string The content to analise.
     * @return int A percentage.
     * @since 1.0
     */
    public static function binaryPercentage($sContent) {
    	if (strlen($sContent) == 0) return 0;
        $iBinary = 0;
        $aContent = str_split($sContent);
        foreach ($aContent as $cCharacter) {
        	$iOrd = ord($cCharacter);
            $iBinary += ($iOrd >= 32 && $iOrd <= 125) ? 0 : 1;
        }
        return 100 * ($iBinary / strlen($sContent));
    }

}

?>
