module.exports = {
	"env": {
		"browser": true,
		"commonjs": true,
		"es6": true,
	},
	"parser": "babel-eslint",
	"extends": "eslint:recommended",
	"parserOptions": {
		"ecmaVersion": 2016,
		"sourceType": "module"
	},
	"rules": {
		"indent": [
			"off",
			"tab",
		],
		"linebreak-style": [
			"off",
			"unix",
		],
		"quotes": [
			"error",
			"single",
		],
		"semi": [
			"error",
			"always",
		],
	},
};
