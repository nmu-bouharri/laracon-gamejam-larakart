<script lang="ts" setup></script>

<template>
    <html lang="en">
        <head>
            <meta charset="UTF-8" />
            <meta
                name="viewport"
                content="width=device-width, initial-scale=1.0"
            />
        </head>
        <body class="gradient-bg min-h-screen text-white">
            <div class="container mx-auto px-4 py-8">
                <slot />
            </div>
        </body>
    </html>
</template>
