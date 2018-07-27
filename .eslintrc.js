module.exports = {
    "plugins": [
        "html"
    ],
    "env": {
        "browser": true,
        "es6": true
    },
    "extends": "eslint:recommended",
    "parserOptions": {
        "ecmaFeatures": {
            "jsx": true
        },
        "sourceType": "module"
    },
    "rules": {
        "indent": [
            "error",//off/warn/error
            "tab"
        ],
        "linebreak-style": [
            "off",
            "windows"
        ],
        "quotes": [
            "off",
            "double"
        ],
        "semi": [
            "off",
            "always"
		],
		'no-console': 'off'
    },
    "globals": {
		"global": false,
		"$": false,
		"_": false,
		"Site": false,
		"module": false,
		"require": false
    }
};
