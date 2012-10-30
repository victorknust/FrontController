# Arrouter

Trying out the Front Controller pattern in PHP.

## Usage

    <?php

    $app = Arrouter::app();

    $app->get('/form', function() {
            ?>
                <form action="greeting" method="post">
                    <input type="text" name="location" value="World">
                    <input type="submit">
                </form>
            <?php
        }, $app::STRICT_MATCHING);

    $app->post('/greeting', function() {
            echo "Hello, {$this->post['location']}!";
        });

    $app->get('/', function() {
            echo "Nothing here, bud.";
        });

    $result = $app->run();

    if ($result->code === 404) {
        echo "Not found";
    }
