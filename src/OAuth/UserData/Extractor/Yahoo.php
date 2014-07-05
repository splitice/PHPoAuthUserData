<?php

/*
 * This file is part of the Oryzone PHPoAuthUserData package <https://github.com/Oryzone/PHPoAuthUserData>.
 *
 * (c) Oryzone, developed by Luciano Mammino <lmammino@oryzone.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace OAuth\UserData\Extractor;

use OAuth\UserData\Utils\ArrayUtils;
use OAuth\UserData\Utils\StringUtils;


/**
 * Class Yahoo
 * @package OAuth\UserData\Extractor
 */
class Yahoo extends LazyExtractor
{
    /*
     Format:
object(stdClass)[81]
  public 'profile' =>
    object(stdClass)[84]
      public 'guid' => string '' (length=26)
      public 'addresses' =>
        array (size=2)
          0 =>
            object(stdClass)[83]
              public 'city' => string '' (length=0)
              public 'country' => string 'AU' (length=2)
              public 'current' => boolean true
              public 'id' => int 1
              public 'postalCode' => string '' (length=0)
              public 'state' => string '' (length=0)
              public 'street' => string '' (length=0)
              public 'type' => string 'HOME' (length=4)
          1 =>
            object(stdClass)[136]
              public 'city' => string '' (length=0)
              public 'country' => string 'AU' (length=2)
              public 'current' => boolean true
              public 'id' => int 2
              public 'postalCode' => string '' (length=0)
              public 'state' => string '' (length=0)
              public 'street' => string '' (length=0)
              public 'type' => string 'WORK' (length=4)
      public 'ageCategory' => string 'A' (length=1)
      public 'created' => string '2014-07-05T09:47:21Z' (length=20)
      public 'emails' =>
        array (size=1)
          0 =>
            object(stdClass)[140]
              public 'handle' => string '' (length=19)
              public 'id' => int 1
              public 'primary' => boolean true
              public 'type' => string 'HOME' (length=4)
      public 'familyName' => string '' (length=5)
      public 'gender' => string 'M' (length=1)
      public 'givenName' => string '' (length=6)
      public 'image' =>
        object(stdClass)[139]
          public 'height' => int 192
          public 'imageUrl' => string 'https://s.yimg.com/dh/ap/social/profile/profile_b192.png' (length=56)
          public 'size' => string '192x192' (length=7)
          public 'width' => int 192
      public 'intl' => string 'au' (length=2)
      public 'jurisdiction' => string 'au' (length=2)
      public 'lang' => string 'en-AU' (length=5)
      public 'memberSince' => string '2014-07-04T11:07:02Z' (length=20)
      public 'migrationSource' => int 1
      public 'nickname' => string '' (length=6)
      public 'notStored' => boolean true
      public 'nux' => string '0' (length=1)
      public 'phones' =>
        array (size=1)
          0 =>
            object(stdClass)[137]
              public 'id' => int 10
              public 'number' => string '11' (length=12)
              public 'type' => string 'MOBILE' (length=6)
      public 'profileMode' => string 'PUBLIC' (length=6)
      public 'profileStatus' => string 'ACTIVE' (length=6)
      public 'profileUrl' => string 'http://profile.yahoo.com/W3Y2HLXUJLNXWUAMWDGU2RYABM' (length=51)
      public 'timeZone' => string 'Australia/Sydney' (length=16)
      public 'isConnected' => boolean true
      public 'profileHidden' => boolean false
      public 'bdRestricted' => boolean true
      public 'profilePermission' => string 'PRIVATE' (length=7)
      public 'uri' => string 'https://social.yahooapis.com/v1/user/W3Y2HLXUJLNXWUAMWDGU2RYABM/profile' (length=71)
      public 'cache' => boolean true
 */

    /**
     * Request contants
     */
    const REQUEST_GUID = 'me/guid?format=json';
    const REQUEST_PROFILE = 'user/%s/profile?format=json';

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct(self::getLoadersMap(), self::getNormalizersMap(), self::getAllFields());
    }

    protected static function getLoadersMap()
    {
        return self::getDefaultLoadersMap();
    }

    public static function getNormalizersMap()
    {
        return array_merge(self::getDefaultNormalizersMap());
    }

    protected function profileLoader()
    {
        $guid = json_decode($this->service->request(sprintf(self::REQUEST_GUID,$guid->guid->value)));

        $request = json_decode($this->service->request(sprintf(self::REQUEST_PROFILE,$guid->guid->value)), true);

        return $request['profile'];
    }

    protected function imageLoader()
    {
        $rawPicture = json_decode($this->service->request(self::REQUEST_IMAGE), true);
        if (isset($rawPicture['data'], $rawPicture['data']['url'])) {
            return $rawPicture['data']['url'];
        }

        return null;
    }

    protected function uniqueIdNormalizer($data)
    {
        return $data['guid'];
    }

    protected function usernameNormalizer($data)
    {
        return isset($data['nickname']) ? $data['nickname'] : null;
    }

    protected function firstNameNormalizer($data)
    {
        return isset($data['givenName']) ? $data['givenName'] : null;
    }

    protected function lastNameNormalizer($data)
    {
        return isset($data['familyName']) ? $data['familyName'] : null;
    }

    protected function fullNameNormalizer($data)
    {
        return (isset($data['givenName']) && isset($data['familyName'])) ? ($data['givenName']. ' '. $data['familyName']) : null;
    }

    protected function emailNormalizer($data)
    {
        return isset($data['emails'][0]) ? $data['emails'][0]['handle'] : null;
    }

    protected function descriptionNormalizer($data)
    {
        return isset($data['bio']) ? $data['bio'] : null;
    }

    protected function profileUrlNormalizer($data)
    {
        return isset($data['link']) ? $data['link'] : null;
    }

    protected function locationNormalizer($data)
    {
        return isset($data['location']['name']) ? $data['location']['name'] : null;
    }

    protected function websitesNormalizer($data)
    {
        return isset($data['website']) ? StringUtils::extractUrls($data['website']) : array();
    }

    protected function imageUrlNormalizer($data)
    {
        return isset($data['picture']) ? $data['picture'] : null;
    }

    public function verifiedEmailNormalizer()
    {
        return true; // Google is an email!
    }

    protected function extraNormalizer($data)
    {
        return ArrayUtils::removeKeys($data, array(
            'id',
            'given_name',
            'family_name',
            'name',
            'email',
            'link',
            'location',
        ));
    }
}
