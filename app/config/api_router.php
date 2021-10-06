<?php
declare(strict_types = 1);

return \FastRoute\simpleDispatcher(function(\FastRoute\RouteCollector $r) {
    $r->addRoute(
        ['GET'],
        '/cockpit/{command:.+}',
        \EventEngine\EeCockpit\EeCockpitHandler::class
    );

    $r->addRoute(
        ['POST'],
        '/messagebox',
        \Acme\Http\Handler\MessageBoxHandler::class
    );

    $r->addRoute(
        ['POST'],
        '/messagebox/{message_name:[A-Za-z0-9_.-\/]+}',
        \Acme\Http\Handler\MessageBoxHandler::class
    );

    $r->addRoute(
        ['GET'],
        '/messagebox-schema',
        \Acme\Http\Handler\MessageSchemaHandler::class
    );
});
