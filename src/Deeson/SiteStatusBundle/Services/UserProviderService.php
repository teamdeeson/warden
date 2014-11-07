<?php
namespace Deeson\SiteStatusBundle\Services;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\User;
use \Symfony\Component\Yaml\Yaml;

class UserProviderService implements UserProviderInterface {

  protected $siteConfigFile = '';

  public function __construct($siteConfigFile) {
    $this->siteConfigFile = $siteConfigFile;
  }

  public function loadUserByUsername($username) {
    if (!file_exists($this->siteConfigFile)) {
      throw new UsernameNotFoundException(sprintf("Username %s not found", $username));
    }

    $siteConfig = Yaml::parse(file_get_contents($this->siteConfigFile));
    foreach ($siteConfig['users'] as $name => $userData) {
      if ($name == $username) {
        $roles = $userData['roles'];
        if (count($roles) < 1) {
          $roles = array('ROLE_USER');
        }
        return new User($name, $userData['pass'], $roles);
      }
    }
    throw new UsernameNotFoundException(sprintf("Username %s not found", $username));
  }

  public function refreshUser(UserInterface $user) {
    return $this->loadUserByUsername($user->getUsername());
  }

  public function supportsClass($class) {
    return $class === 'Symfony\Component\Security\Core\User\User';
  }

}