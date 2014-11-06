<?php
namespace Deeson\SiteStatusBundle\Services;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\User;
use \Symfony\Component\Yaml\Yaml;

class UserProviderService implements UserProviderInterface {

  protected $userConfigFile = '';

  public function __construct($user_config_file) {
    $this->userConfigFile = $user_config_file;
  }

  public function loadUserByUsername($username) {
    if (!file_exists($this->userConfigFile)) {
      throw new UsernameNotFoundException(sprintf("Username %s not found", $username));
    }
    $users = Yaml::parse(file_get_contents($this->userConfigFile));
    foreach ($users as $name => $userData) {
      if ($name == $username) {
        $user = new User($name, $userData['pass'], $userData['roles']);
        return $user;
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