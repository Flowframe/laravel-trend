<?php

it('does not use debugging functions')
    ->expect(['dd', 'dump', 'ray', 'log'])
    ->each->not->toBeUsed();
