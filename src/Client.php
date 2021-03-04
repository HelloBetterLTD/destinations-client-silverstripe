<?php
/**
 * Created by Nivanka Fonseka (nivanka@silverstripers.com).
 * User: nivankafonseka
 * Date: 8/21/19
 * Time: 1:35 PM
 * To change this template use File | Settings | File Templates.
 */

namespace DD\Client\SilverStripe;

use DD\Client\Core\Client as SS_Client;
use Psr\SimpleCache\CacheInterface;
use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Flushable;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Security\Security;

class Client extends SS_Client implements Flushable
{
	use Configurable;

	private static $destinations_endpoint = '';
	private static $destinations_key = '';
	private static $referrer = '';
	private static $inst = null;
	private static $remove_cache_when_logged_in = false;
	private static $exclude_categories_for_nearby = true;

	/**
	 * @return Client
	 */
	public static function inst()
	{
		if (!self::$inst) {
			SS_Client::set_end_point(self::config()->get('destinations_endpoint'));
			SS_Client::set_key(self::config()->get('destinations_key'));
			self::$inst = new Client(
				self::config()->get('destinations_key'),
				self::config()->get('destinations_endpoint'),
				Director::host()
			);
		}
		return self::$inst;
	}

	public static function flush()
	{
		$cache = Injector::inst()->get(CacheInterface::class . '.DDClient');
		$cache->clear();
	}

	/**
	 * @return CacheInterface
	 */
	public static function get_cache()
	{
		return Injector::inst()->get(CacheInterface::class . '.DDClient');
	}

	public function call($query, $vars, $queryName = null)
	{
		$options = $this->makeOptions($query, $vars);
		$cacheKey = md5(json_encode($options) . '__' . $queryName);
		$cache = self::get_cache();
		$json = $cache->get($cacheKey);
		$call = !$json || Director::isDev();
		if (Security::getCurrentUser() && self::config()->get('remove_cache_when_logged_in')) {
		    $call = true;
        }
		if ($call) {
			try {
                $response = $this->getClient()->post(
                    self::get_end_point(),
                    $options
                );

                $json = $response->getBody()->getContents();
                if ($response->getStatusCode() !== 200) {
                    return [];
                }
                $result = json_decode($json, true);
                if (!isset($result['data'][$queryName])) {
                    return [];
                }
                $cache->set($cacheKey, $json);
            } catch (\Exception $e) {
			    return null;
            }
		} else {
			$result = json_decode($json, true);
		}

		if (!isset($result['data'][$queryName])) {
			return [];
		}
		return $result['data'][$queryName];

	}

}
