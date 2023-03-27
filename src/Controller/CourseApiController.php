<?php

namespace App\Controller;

use App\Common\Database\ConnectionProvider;
use App\Common\Database\Synchronization;
use App\Controller\Request\CourseApiRequestParser;
use App\Controller\Request\RequestValidationException;
use App\Model\Service\ServiceProvider;
use JsonException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Throwable;

class CourseApiController
{
    private const HTTP_STATUS_OK = 200;
    private const HTTP_STATUS_BAD_REQUEST = 400;
    private const HTTP_STATUS_CONFLICT = 409;


    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     * @throws Throwable
     */
    public function saveCourse(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $params = CourseApiRequestParser::parseSaveCourseArticleParams((array)$request->getParsedBody());
        } catch (RequestValidationException $exception) {
            return $this->badRequest($response, $exception->getFieldErrors());
        }

        $synchronization = new Synchronization(ConnectionProvider::getConnection());
        $synchronization->doWithTransaction(function () use ($params) {
            ServiceProvider::getInstance()->getCourseRepository()->save($params);
        });

        return $this->success($response, []);
    }

    private function success(ResponseInterface $response, array $responseData): ResponseInterface
    {
        return $this->withJson($response, $responseData)->withStatus(self::HTTP_STATUS_OK);
    }

    private function badRequest(ResponseInterface $response, array $errors): ResponseInterface
    {
        $responseData = ['errors' => $errors];
        return $this->withJson($response, $responseData)->withStatus(self::HTTP_STATUS_BAD_REQUEST);
    }

    private function withJson(ResponseInterface $response, array $responseData): ResponseInterface
    {
        try {
            $responseBytes = json_encode($responseData, JSON_THROW_ON_ERROR);
            $response->getBody()->write($responseBytes);
            return $response->withHeader('Content-Type', 'application/json');
        } catch (JsonException $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
