<?php

/**
 * Quantum PHP Framework
 *
 * An open source software development framework for PHP
 *
 * @package Quantum
 * @author Arman Ag. <arman.ag@softberg.org>
 * @copyright Copyright (c) 2018 Softberg LLC (https://softberg.org)
 * @link http://quantum.softberg.org/
 * @since 2.6.0
 */

namespace Modules\Api\Middlewares;

use Quantum\Factory\ModelFactory;
use Quantum\Libraries\Validation\Validator;
use Quantum\Libraries\Validation\Rule;
use Quantum\Middleware\QtMiddleware;
use Quantum\Http\Response;
use Quantum\Http\Request;
use Shared\Models\User;
use Quantum\Di\Di;

/**
 * Class Forget
 * @package Modules\Api\Middlewares
 */
class Forget extends QtMiddleware
{

    /**
     * @var \Quantum\Libraries\Validation\Validator
     */
    private $validator;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->validator = new Validator();

        $this->validator->addRule('email', [
            Rule::set('required'),
            Rule::set('email')
        ]);
    }

    /**
     * @param \Quantum\Http\Request $request
     * @param \Quantum\Http\Response $response
     * @param \Closure $next
     * @return mixed
     */
    public function apply(Request $request, Response $response, \Closure $next)
    {
        if ($request->isMethod('post')) {
            if (!$this->validator->isValid($request->all())) {
                $response->json([
                    'status' => 'error',
                    'message' => $this->validator->getErrors()
                ]);
                
                stop();
            }

            if (!$this->emailExists($request->get('email'))) {
                $response->json([
                    'status' => 'error',
                    'message' => [t('validation.nonExistingRecord', $request->get('email'))]
                ]);
                
                stop();
            }
        }

        return $next($request, $response);
    }

    /**
     * Check for email existence
     * @param string $email
     * @return bool
     */
    private function emailExists(string $email): bool
    {
        $modelFactory = Di::get(ModelFactory::class);
        $userModel = $modelFactory->get(User::class);

        return !empty($userModel->findOneBy('email', $email)->asArray());
    }

}
