<?php

namespace Alnaggar\Muhawil\Traits;

trait HasPluralForms
{
    /**
     * Retrieve plural forms for a specific language.
     * 
     * @param string $locale
     * @return string
     */
    protected function getPluralForms(string $language) : string
    {
        $language = str_replace('-', '_', $language);
        $language = strstr($language, '_', true) ?: $language;

        switch ($language) {
            // Singular only languages
            case 'bo':
            case 'dz':
            case 'id':
            case 'ja':
            case 'ka':
            case 'km':
            case 'ko':
            case 'ms':
            case 'th':
            case 'vi':
            case 'zh':
                return 'nplurals=1; plural=0;';

            // Languages with n != 1 rule
            case 'az':
            case 'af':
            case 'bn':
            case 'bg':
            case 'ca':
            case 'da':
            case 'de':
            case 'el':
            case 'en':
            case 'eo':
            case 'es':
            case 'et':
            case 'eu':
            case 'fi':
            case 'fo':
            case 'fur':
            case 'fy':
            case 'gl':
            case 'gu':
            case 'ha':
            case 'he':
            case 'hu':
            case 'it':
            case 'kn':
            case 'ku':
            case 'lb':
            case 'ml':
            case 'mn':
            case 'mr':
            case 'nah':
            case 'nb':
            case 'ne':
            case 'nl':
            case 'nn':
            case 'no':
            case 'om':
            case 'or':
            case 'pa':
            case 'pap':
            case 'ps':
            case 'pt':
            case 'so':
            case 'sq':
            case 'sv':
            case 'sw':
            case 'ta':
            case 'te':
            case 'tk':
            case 'ur':
            case 'zu':
            case 'bh':
            case 'hi':
            case 'hy':
            case 'nso':
            case 'xbr':
                return 'nplurals=2; plural=(n != 1);';

            // Languages with n > 1 rule
            case 'tr':
            case 'fa':
            case 'am':
            case 'fil':
            case 'fr':
            case 'gun':
            case 'ln':
            case 'mg':
            case 'ti':
            case 'wa':
                return 'nplurals=2; plural=(n > 1);';

            // Languages with other plural rules
            case 'jv':
                return 'nplurals=2; plural=(n != 0);';
            case 'is':
                return 'nplurals=2; plural=(n%10!=1 || n%100==11);';
            case 'mk':
                return 'nplurals=2; plural=(n==1 || n%10==1) ? 0 : 1;';
            case 'be':
            case 'bs':
            case 'hr':
            case 'ru':
            case 'sr':
            case 'uk':
                return 'nplurals=3; plural=(n%10==1 && n%100!=11 ? 0 : n%10>=2 && n%10<=4 && (n%100<10 || n%100>=20) ? 1 : 2);';
            case 'cs':
            case 'sk':
                return 'nplurals=3; plural=(n==1) ? 0 : (n>=2 && n<=4) ? 1 : 2;';
            case 'lt':
                return 'nplurals=3; plural=(n%10==1 && n%100!=11) ? 0 : (n%10>=2 && (n%100<10 || n%100>=20)) ? 1 : 2;';
            case 'lv':
                return 'nplurals=3; plural=(n%10==1 && n%100!=11) ? 0 : (n!=0) ? 1 : 2;';
            case 'pl':
                return 'nplurals=3; plural=(n==1) ? 0 : (n%10>=2 && n%10<=4 && (n%100<10 || n%100>=20)) ? 1 : 2;';
            case 'ro':
                return 'nplurals=3; plural=(n==1) ? 0 : (n==0 || (n%100>0 && n%100<20)) ? 1 : 2;';
            case 'sl':
                return 'nplurals=4; plural=(n%100==1) ? 0 : (n%100==2) ? 1 : (n%100==3 || n%100==4) ? 2 : 3;';
            case 'mt':
                return 'nplurals=4; plural=(n==1) ? 0 : (n==0 || (n%100>1 && n%100<11)) ? 1 : (n%100>10 && n%100<20) ? 2 : 3;';
            case 'cy':
                return 'nplurals=4; plural=(n==1) ? 0 : (n==2) ? 1 : (n!=8 && n!=11) ? 2 : 3;';
            case 'ga':
                return 'nplurals=5; plural=n==1 ? 0 : n==2 ? 1 : (n>2 && n<7) ? 2 :(n>6 && n<11) ? 3 : 4;';
            case 'ar':
                return 'nplurals=6; plural=(n==0) ? 0 : (n==1) ? 1 : (n==2) ? 2 : (n%100>=3 && n%100<=10) ? 3 : (n%100>=11 && n%100<=99) ? 4 : 5;';

            default:
                // Default case should return a common plural form rule
                return 'nplurals=2; plural=(n != 1);';
        }
    }
}
