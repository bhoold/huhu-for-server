module.exports = {
    "plugins": [],
    "env": {
		"node": true
    },
    "extends": "eslint:recommended",
    "parserOptions": {
		"ecmaVersion": 8,
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

    }
};
