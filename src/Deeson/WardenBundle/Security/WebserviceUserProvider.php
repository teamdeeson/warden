<?php
namespace Deeson\WardenBundle\Security;

use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Yaml\Yaml;

class WebserviceUserProvider implements UserProviderInterface {

  protected $securityConfigurationFile = '';

  public function __construct($appDir) {
    $this->securityConfigurationFile = $appDir . '/config/warden-security.yml';
  }

  /**
   * @param string $username
   * @return UserInterface|void
   */
  public function loadUserByUsername($username) {
    if (!file_exists($this->securityConfigurationFile)) {
      throw new UsernameNotFoundException(sprintf("Username %s not found", $username));
    }

    $siteConfig = Yaml::parse(file_get_contents($this->securityConfigurationFile));
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
    if (!$user instanceof User) {
      throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
    }

    return $this->loadUserByUsername($user->getUsername());
  }

  public function supportsClass($class) {
    return User::class === $class;
  }

  /**
   * Determine if setup has already been done
   *
   * @return bool
   *   True if warden thinks it is already setup.
   */
  public function isSetup() {
    return file_exists($this->securityConfigurationFile);
  }

  /**
   * Generate the config files for the application.
   *
   * @param string $username
   * @param string $password
   *
   * @throws \Exception
   */
  public function generateLoginFile($username, $password) {
    if ($password == '') {
      throw new \Exception('Password cannot be empty');
    }

    $configData = array(
      'users' => array(
        $username => array(
          'pass' => hash('sha512', $password),
          'roles' => array(
            'ROLE_USER'
          )
        )
      )
    );

    $siteConfig = Yaml::dump($configData);
    file_put_contents($this->securityConfigurationFile, $siteConfig);
  }

}
