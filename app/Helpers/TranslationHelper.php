<?php
/**
 * Invoice Ninja (https://invoiceninja.com)
 *
 * @link https://github.com/invoiceninja/invoiceninja source repository
 *
 * @copyright Copyright (c) 2019. Invoice Ninja LLC (https://invoiceninja.com)
 *
 * @license https://opensource.org/licenses/AAL
 */

use Illuminate\Support\Facades\Cache;

/**
 * Returns a custom translation string
 * falls back on defaults if no string exists
 *
 * //Cache::forever($custom_company_translated_string, 'mogly');
 * 
 * @param  string translation string key
 * @return string
 */
function ctrans(string $string) : string
{
	//todo pass through the cached version of the custom strings here else return trans();
	
    return trans($string);

}