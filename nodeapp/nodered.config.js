/**
 * Get compatible PM2 app config object with automatic support for .nvmrc, 
 * and port allocation.
 */
module.exports = {
    apps: (function() {
        let nodeapp = require('/usr/local/hestia/plugins/nodeapp/nodeapp.js')(__filename);
        nodeapp.linkGlobalModules( ['node-red'] );
        nodeapp.script = nodeapp.cwd + '/node_modules/node-red/red.js';
        nodeapp.args += ' -u ' + __dirname;
        return [nodeapp];
    })()
}
