<?php

it('uses full page links for blade pages from inertia layouts', function () {
    $layouts = [
        resource_path('js/layouts/PublicLayout.svelte'),
        resource_path('js/layouts/auth/AuthCardLayout.svelte'),
        resource_path('js/layouts/auth/AuthSimpleLayout.svelte'),
        resource_path('js/layouts/auth/AuthSplitLayout.svelte'),
    ];

    foreach ($layouts as $layoutPath) {
        $layout = file_get_contents($layoutPath);

        expect($layout)
            ->not->toContain('<Link\n                    href="/"')
            ->not->toContain('<Link\n                        href="/aktualnosci"')
            ->not->toContain('href={home()}');
    }
});
