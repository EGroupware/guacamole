/**
 * EGroupware - Guacamole - Hooks
 *
 * @link http://www.egroupware.org
 * @author Ralf Becker <rb-AT-egroupware.org>
 * @package guacamole
 * @copyright (c) 2020 by Ralf Becker <rb-AT-egroupware.org>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 */

app.classes.guacamole = AppJS.extend(
{
    appname: 'guacamole',

    /**
     * Initialize javascript for this application
     *
     * @memberOf mail
     */
    init: function ()
    {
        this._super.apply(this, arguments);

        if (!this.egw.is_popup()) {

        }
    },

    /**
     * Destructor
     */
    destroy: function ()
    {
        // call parent
        this._super.apply(this, arguments);
    },

    /**
     * This function is called when the etemplate2 object is loaded
     * and ready.  If you must store a reference to the et2 object,
     * make sure to clean it up in destroy().
     *
     * @param et2 etemplate2 Newly ready object
     * @param {string} _name template name
     */
    et2_ready: function (et2, _name)
    {
        // call parent; somehow this function is called more often. (twice on a display and compose) why?
        this._super.apply(this, arguments);

        switch (_name)
        {
            case 'guacamole.index':
                // Attempt to refocus iframe upon click or keydown
                document.addEventListener('click', jQuery.proxy(this.refocusGuacamole, this));
                document.addEventListener('keydown', jQuery.proxy(this.refocusGuacamole, this));
                break;
        }
    },

    /**
     * Load a link into Guacamole iframe
     *
     * @param _url
     */
    load: function(_url)
    {
        let iframe = this.et2.getWidgetById('guacamole');
        // just setting iframe src for everything else get's Guacamole in an unresponsive state
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
    },

    /**
     * Refocuses the iframe containing Guacamole if the user is not already
     * focusing another non-body element on the page.
     */
    refocusGuacamole: function()
    {
        // Do not refocus if focus is on an input field
        if (document.activeElement !== document.body ||
            // or Guacamole is not visiable
            framework && framework.activeApp.appName !== 'guacamole')
        {
            return;
        }
        // Ensure iframe is focused
        let iframe = this.et2.getWidgetById('guacamole');
        if (iframe) iframe.getDOMNode().focus();
    }
});