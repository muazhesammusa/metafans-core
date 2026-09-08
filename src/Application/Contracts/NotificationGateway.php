<?php
/** Social notification boundary. */
namespace METAFANSCORE\Application\Contracts;
defined( 'ABSPATH' ) || exit;
interface NotificationGateway { public function activity_comment( int $activity_id, int $actor_id, string $action ): void; }
