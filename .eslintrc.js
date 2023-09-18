module.exports = {
    parser: '@babel/eslint-parser',
    'parserOptions': {
        'requireConfigFile': false,
        'ecmaVersion': 6,
        'sourceType': 'module',
        'ecmaFeatures': {
            'jsx': true,
            'experimentalObjectRestSpread': true
        },
        'babelOptions': {
            'presets': ['@babel/preset-react']
        },
    },
    'extends': [
        'eslint:recommended'
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
          ecmaFeatures: { jsx: true },
          project: ["tsconfig.json"],
          tsconfigRootDir: './',
          sourceType: 'module',
        },
    }]
};