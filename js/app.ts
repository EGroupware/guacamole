/**
 * EGroupware - Guacamole - Hooks
 *
 * @link: https://www.egroupware.org
 * @author Ralf Becker <rb-AT-egroupware.org>
 * @package guacamole
 * @copyright (c) 2020-23 by Ralf Becker <rb-AT-egroupware.org>
 * @license https://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 */

import { EgwApp } from '../../api/js/jsapi/egw_app';
import type {etemplate2} from "../../api/js/etemplate/etemplate2";
import type {Et2Iframe} from "../../api/js/etemplate/Et2Iframe/Et2Iframe";
import {egw, framework, app} from "../../api/js/jsapi/egw_global";


class GuacamoleApp extends EgwApp
{
    /**
     * Initialize this application
     */
    constructor()
    {
        super('guacamole');

        if (!this.egw.is_popup()) {

        }
    }

    /**
     * Destructor
     */
    destroy(_app)
    {
        // call parent
        super.destroy(_app);
    }

    /**
     * This function is called when the etemplate2 object is loaded
     * and ready.  If you must store a reference to the et2 object,
     * make sure to clean it up in destroy().
     *
     * @param _et2 etemplate2 Newly ready object
     * @param _name template name
     */
    et2_ready(_et2 : etemplate2, _name : string)
    {
        // call parent; somehow this function is called more often. (twice on a display and compose) why?
        super.et2_ready(_et2, _name);

        switch (_name)
        {
            case 'guacamole.index':
                // Attempt to refocus iframe upon click or keydown
                document.addEventListener('click', this.refocusGuacamole.bind(this));
                document.addEventListener('keydown', this.refocusGuacamole.bind(this));
                break;
        }
    }

    /**
     * Load a link into Guacamole iframe
     *
     * @param _url
     */
    load(_url : string)
    {
        let iframe = <Et2Iframe><any>this.et2.getWidgetById('guacamole');
        // just setting iframe src for everything else gets Guacamole in an unresponsive state
        // probably would need to call some link-handler from angular.js ...
        if (iframe && _url === '/guacamole/')
        {
            iframe.set_src(_url);
        }
        else if (framework && framework.linkHandler)
        {
            let link = egw.link('/index.php', {
                menuaction: 'guacamole.EGroupware\\Guacamole\\Ui.index',
                load: _url,
                ajax: 'true'
            });
            framework.linkHandler(link, this.appname, false);
        }
    }

    /**
     * Refocuses the iframe containing Guacamole if the user is not already
     * focusing another non-body element on the page.
     */
    refocusGuacamole()
    {
        // Do not refocus if focus is on an input field
        if (document.activeElement !== document.body ||
            // or Guacamole is not visible
            framework && framework.activeApp.appName !== 'guacamole')
        {
            return;
        }
        // Ensure iframe is focused
        let iframe = <Et2Iframe><any>this.et2.getWidgetById('guacamole');
        if (iframe) iframe.getDOMNode().focus();
    }
}

app.classes.guacamole = GuacamoleApp;