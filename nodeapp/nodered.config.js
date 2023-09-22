module.exports = {
    apps: (function() {
        const fs = require('fs');

        // Load default PM2 compatible nodeapp configuration.
        let nodeapp = require('/usr/local/hestia/plugins/nodeapp/nodeapp.js')(__filename);

        /**
         * Specify the Node-RED version to use.
         * 
         * Read the .noderedrc file and find the Node-RED version specified from it,
         * or default to v3.1.0.
         */
        let file = __dirname + '/.noderedrc';
        let ver = 'v3.1.0';
        if (fs.existsSync(file)) {
            ver = fs.readFileSync(file, {encoding:'utf8', flag:'r'}).trim();
        }
        nodeapp.ver = ver;
        nodeapp.script = '/opt/node-red/' + ver + '/lib/node_modules/node-red/red.js';
        nodeapp.args += ' -u ' + __dirname;
        return [nodeapp];
    })()
}
