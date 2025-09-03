<?php

test('example feature test', function () {
    \$response = \$this->get('/');

    \$response->assertStatus(200);
});