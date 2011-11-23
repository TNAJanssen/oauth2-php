<?php

use OAuth2\OAuth2;
use Symfony\Component\HttpFoundation\Request;

/**
 * OAuth2 test cases that invovle capturing output.
 */
class OAuth2OutputTest extends PHPUnit_Framework_TestCase {
  
  /**
   * @var OAuth2
   */
  private $fixture;
  
  /**
   * Tests OAuth2->grantAccessToken() with successful Auth code grant
   * 
   */
  public function testGrantAccessTokenWithGrantAuthCodeSuccess() {
    $request = new Request(
      array('grant_type' => OAuth2::GRANT_TYPE_AUTH_CODE, 'redirect_uri' => 'http://www.example.com/my/subdir', 'client_id' => 'my_little_app', 'client_secret' => 'b', 'code'=> 'foo')
    );
    $storedToken = array('redirect_uri' => 'http://www.example.com', 'client_id' => 'my_little_app', 'expires' => time() + 60);
    
    $mockStorage = $this->createBaseMock('OAuth2\IOAuth2GrantCode');
    $mockStorage->expects($this->any())
      ->method('getAuthCode')
      ->will($this->returnValue($storedToken));
      
    $this->fixture = new OAuth2($mockStorage);
    $response = $this->fixture->grantAccessToken($request);

    // Successful token grant will return a JSON encoded token:
    $this->assertRegexp('/{"access_token":".*","expires_in":\d+,"token_type":"bearer"/', $response->getContent());
  }
  
  /**
   * Tests OAuth2->grantAccessToken() with successful Auth code grant, but without redreict_uri in the input
   */
  public function testGrantAccessTokenWithGrantAuthCodeSuccessWithoutRedirect() {
    $request = new Request(
      array('grant_type' => OAuth2::GRANT_TYPE_AUTH_CODE, 'client_id' => 'my_little_app', 'client_secret' => 'b', 'code'=> 'foo')
    );
    $storedToken = array('redirect_uri' => 'http://www.example.com', 'client_id' => 'my_little_app', 'expires' => time() + 60);
    
    $mockStorage = $this->createBaseMock('OAuth2\IOAuth2GrantCode');
    $mockStorage->expects($this->any())
      ->method('getAuthCode')
      ->will($this->returnValue($storedToken));
      
    $this->fixture = new OAuth2($mockStorage);
    $this->fixture->setVariable(OAuth2::CONFIG_ENFORCE_INPUT_REDIRECT, false); 
    $response = $this->fixture->grantAccessToken($request);

    // Successful token grant will return a JSON encoded token:
    $this->assertRegexp('/{"access_token":".*","expires_in":\d+,"token_type":"bearer"/', $response->getContent());
  }
  
// Utility methods
  
  /**
   * 
   * @param string $interfaceName
   */
  protected function createBaseMock($interfaceName) {
    $mockStorage = $this->getMock($interfaceName);
    $mockStorage->expects($this->any())
      ->method('checkClientCredentials')
      ->will($this->returnValue(TRUE)); // Always return true for any combination of user/pass
    $mockStorage->expects($this->any())
      ->method('checkRestrictedGrantType')
      ->will($this->returnValue(TRUE)); // Always return true for any combination of user/pass
      
     return $mockStorage;
  }
  
}