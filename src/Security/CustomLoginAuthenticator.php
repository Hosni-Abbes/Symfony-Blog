<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Exception;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class CustomLoginAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';
    public const ADMINLOGIN_ROUTE = 'admin_login';

    private UrlGeneratorInterface $urlGenerator;
    private $userRepository;

    public function __construct(UrlGeneratorInterface $urlGenerator, UserRepository $userRepository)
    {
        $this->urlGenerator = $urlGenerator;
        $this->userRepository = $userRepository;
    }

    public function authenticate(Request $request): Passport
    {
        

        $username = $request->request->get('username', '');
        
        $request->getSession()->set(Security::LAST_USERNAME, $username);

        $user = $request->request->get('username');
        
        //If Username not Exist
       if (!$user) throw new CustomUserMessageAuthenticationException('Please Enter a valid credentials!');

        if($this->getUser($user)){
            if($request->getPathInfo() == '/admin-login' && in_array("admin", $this->getUser($user)->getRoles() )) {
                return new Passport(
                    new UserBadge($username),
                    new PasswordCredentials($request->request->get('password', '')),
                    [
                        new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
                        ]
                    );
            }else if($request->getPathInfo() == '/login' && !in_array("admin", $this->getUser($user)->getRoles() )) {
                return new Passport(
                    new UserBadge($username),
                    new PasswordCredentials($request->request->get('password', '')),
                    [
                        new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
                        ]
                    );
            }else{
                throw new UserNotFoundException();
            }
        }else{
            throw new UserNotFoundException();
        }
        
    }
        
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            
            return new RedirectResponse($targetPath);
        }
        
        // For example:
        // return new RedirectResponse($this->urlGenerator->generate('some_route'));
        return new RedirectResponse($this->urlGenerator->generate('app_post'));
        // throw new \Exception('TODO: provide a valid redirect inside '.__FILE__);
        
    }

    protected function getLoginUrl(Request $request): string
    {
        if($request->getPathInfo() == '/login'){
            return $this->urlGenerator->generate(self::LOGIN_ROUTE);
        }else{
            return $this->urlGenerator->generate(self::ADMINLOGIN_ROUTE);
        }
    }
    
    
    public function getUser($username){
        $user = $this->userRepository->findOneBy(['username' => $username]);
        return $user;
        
    }


}
