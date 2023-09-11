module.exports = {
    parser: '@babel/eslint-parser',
    'parserOptions': {
        'ecmaVersion': 6,
        'sourceType': 'module',
        'ecmaFeatures': {
            'jsx': true,
            'experimentalObjectRestSpread': true
        }
    },
    'extends': [
        'eslint:recommended',
        'plugin:@typescript-eslint/recommended',
        'plugin:jest/recommended'
    ],
    'plugins': [
        'import',
        'react',
        'jest'
    ],
    'env': {
        'browser' : true,
        'jest/globals': true
    },
    'globals': {

    },
    // Will look for webpack.config.js to resolve path
    'settings': {
        'import/resolver': {
            'node': {
                'extensions': ['.js', '.jsx']
            }
        }
    },

    'rules': {
        // Your own javascript rules

    },
    overrides: [{
        files: ['*.ts', '*.tsx'],
        parser: '@typescript-eslint/parser',
        plugins: ['@typescript-eslint'],

    parserOptions: {
      ecmaFeatures: { jsx: true }
    },

        /**
         * Typescript Rules
         * https://github.com/bradzacher/eslint-plugin-typescript
         * Enable your own typescript rules.
         */
        rules: {
            // Prevent TypeScript-specific constructs from being erroneously flagged as unused
            '@typescript-eslint/no-unused-vars'         : 'error',
            // Default Semicolon style
            '@typescript-eslint/member-delimiter-style' : 'error',
            // Require a consistent member declaration order
            '@typescript-eslint/member-ordering'        : 'error',
            // Require consistent spacing around type annotations
            '@typescript-eslint/type-annotation-spacing': 'error',
        },
    }]
};