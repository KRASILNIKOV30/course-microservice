<?php

namespace App\Controller;

use _PHPStan_e0e4f009c\Nette\Utils\JsonException;
use App\Controller\Request\CourseApiRequestParser;
use App\Controller\Request\RequestValidationException;
use App\Controller\Response\CourseApiResponseFormatter;
use App\Model\Exception\CourseNotFoundException;
use App\Model\Exception\EnrollmentNotFoundException;
use App\Model\Exception\ModuleStatusNotFoundException;
use App\Model\Service\ServiceProvider;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Throwable;

class CourseApiController
{
    private const HTTP_STATUS_OK = 200;
    private const HTTP_STATUS_BAD_REQUEST = 400;

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     * @throws Throwable
     */
    public function saveCourse(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $params = CourseApiRequestParser::parseSaveCourseParams((array)$request->getParsedBody());
        } catch (RequestValidationException $exception) {
            return $this->badRequest($response, $exception->getFieldErrors());
        }
        ServiceProvider::getInstance()->getCourseService()->saveCourse($params);
        return $this->success($response, []);
    }

    public function saveEnrollment(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $params = CourseApiRequestParser::parseSaveEnrollmentParams((array)$request->getParsedBody());
        } catch (RequestValidationException $exception) {
            return $this->badRequest($response, $exception->getFieldErrors());
        }
        ServiceProvider::getInstance()->getCourseService()->saveEnrollment($params);
        return $this->success($response, []);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     * @throws Throwable
     */
    public function saveModuleStatus(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $params = CourseApiRequestParser::parseSaveModuleStatusParams((array)$request->getParsedBody());
        } catch (RequestValidationException $exception) {
            return $this->badRequest($response, $exception->getFieldErrors());
        }
        ServiceProvider::getInstance()->getCourseService()->saveModuleStatus($params);
        return $this->success($response, []);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     * @throws CourseNotFoundException
     * @throws EnrollmentNotFoundException
     * @throws ModuleStatusNotFoundException
     */
    public function getCourseStatus(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $params = CourseApiRequestParser::parseGetCourseStatusParams($request->getQueryParams());
        } catch (RequestValidationException $exception) {
            return $this->badRequest($response, $exception->getFieldErrors());
        }
        $courseStatus = ServiceProvider::getInstance()->getCourseService()->getCourseStatus($params);
        return $this->success($response, CourseApiResponseFormatter::formatCourseStatus($courseStatus));
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
