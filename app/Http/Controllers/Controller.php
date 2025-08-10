<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="FlashcardPro API",
 *     version="1.0.0",
 *     description="RESTful API for accessing public flashcard decks and their content. This API allows developers to integrate flashcard data into their applications, access public study materials, and build educational tools.",
 *     termsOfService="https://flashcardpro.com/terms",
 *     @OA\Contact(
 *         email="api@flashcardpro.com",
 *         name="FlashcardPro API Support"
 *     ),
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 * 
 * @OA\Server(
 *     url="http://127.0.0.1:8085",
 *     description="Local Development Server"
 * )
 * 
 * @OA\Server(
 *     url="https://api.flashcardpro.com",
 *     description="Production API Server"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="BearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="API-Token",
 *     description="Bearer token authentication. Include your API token in the Authorization header as 'Bearer <your-api-token>'."
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="ApiKeyAuth",
 *     type="apiKey",
 *     in="header",
 *     name="X-API-Key",
 *     description="API Key authentication (legacy support). Include your API key in the X-API-Key header."
 * )
 * 
 * @OA\Tag(
 *     name="Decks",
 *     description="Operations related to flashcard decks"
 * )
 * 
 * @OA\Tag(
 *     name="Flashcards", 
 *     description="Operations related to individual flashcards"
 * )
 * 
 * @OA\Tag(
 *     name="Search",
 *     description="Search operations for finding decks"
 * )
 */
abstract class Controller
{
}
