<?php

namespace App\Framework\Http\Controllers;

/**
 * @OA\Info(
 *     title="Event Hub API",
 *     version="1.0.0",
 *     description="API para gerenciamento de eventos com venda de ingressos, processamento de pagamentos e notificações.",
 *     @OA\Contact(
 *         email="contato@eventhub.com"
 *     )
 * )
 *
 * @OA\Server(
 *     url="http://localhost/api/v1",
 *     description="Servidor local"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Autenticação via token Bearer"
 * )
 *
 * @OA\Tag(
 *     name="Auth",
 *     description="Autenticação e autorização"
 * )
 *
 * @OA\Tag(
 *     name="Users",
 *     description="Gerenciamento de usuários"
 * )
 *
 * @OA\Tag(
 *     name="Events",
 *     description="Gerenciamento de eventos"
 * )
 *
 * @OA\Tag(
 *     name="Orders",
 *     description="Gerenciamento de pedidos e ingressos"
 * )
 *
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     required={"id","name","email","type","created_at","updated_at"},
 *     @OA\Property(property="id", type="string", example="ulid"),
 *     @OA\Property(property="name", type="string", example="João Silva"),
 *     @OA\Property(property="email", type="string", format="email", example="joao@example.com"),
 *     @OA\Property(property="type", type="string", example="ORGANIZER"),
 *     @OA\Property(property="created_at", type="string", example="2025-10-20 12:00:00"),
 *     @OA\Property(property="updated_at", type="string", example="2025-10-20 12:00:00")
 * )
 */
class SwaggerController extends Controller
{
    //
}
