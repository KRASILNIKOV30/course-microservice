<?php

namespace App\Controller;

use App\Controller\Request\CourseApiRequestParser;
use App\Controller\Request\RequestValidationException;
use App\Controller\Response\CourseApiResponseFormatter;
use App\Model\Exception\CourseNotFoundException;
use App\Model\Exception\ModuleStatusNotFoundException;
use App\Model\Service\ServiceProvider;
use Exception;
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
     * @throws ModuleStatusNotFoundException
     * @throws \Doctrine\DBAL\Exception
     */
    public function getCourseStatus(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $params = CourseApiRequestParser::parseGetCourseStatusParams($request->getQueryParams());
        } catch (RequestValidationException $exception) {
            return $this->badRequest($response, $exception->getFieldErrors());
        }
        $courseStatus = ServiceProvider::getInstance()->getCourseService()->getCourseStatusData($params);
        return $this->success($response, CourseApiResponseFormatter::formatCourseStatus($courseStatus));
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     * @throws Exception
     */
    public function getModule(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $moduleId = CourseApiRequestParser::parseString($request->getQueryParams(), 'moduleId', 36);
        } catch (RequestValidationException $exception) {
            return $this->badRequest($response, $exception->getFieldErrors());
        }
        $module = ServiceProvider::getInstance()->getCourseService()->getModule($moduleId);

        return $this->success($response, CourseApiResponseFormatter::formatModule($module));
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     * @throws Throwable
     */
    public function deleteCourse(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $courseId = CourseApiRequestParser::parseString($request->getQueryParams(), 'courseId', 36);
        } catch (RequestValidationException $exception) {
            return $this->badRequest($response, $exception->getFieldErrors());
        }
        ServiceProvider::getInstance()->getCourseService()->deleteCourse($courseId);
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
        } catch (\JsonException $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
