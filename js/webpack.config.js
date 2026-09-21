const base = require('flarum-webpack-config')();

module.exports = [
  { ...base, entry: './forum.js', output: { ...base.output, clean: false, filename: 'forum.js' } },
  { ...base, entry: './admin.js', output: { ...base.output, clean: false, filename: 'admin.js' } },
];
