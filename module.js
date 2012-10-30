/**
 * *************************************************************************
 * *                 OOHOO Tab topics Course format                       **
 * *************************************************************************
 * @package     format                                                    **
 * @subpackage  tabtopics                                                 **
 * @name        tabtopics                                                 **
 * @copyright   oohoo.biz                                                 **
 * @link        http://oohoo.biz                                          **
 * @author      Nicolas Bretin                                            **
 * @author      Braedan Jongerius                                         **
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later  **
 * *************************************************************************
 * ************************************************************************ */
M.tabtopics=
{
    init : function(Y)
    {
        Y.use('tabview', function(Y)
        {
            var tabview = new Y.TabView(
            {
                srcNode: '#sections'
            });
            
            tabview.render();
            
            //get the URL param to select the good section by  default
            var url=document.URL.split('#');
            if(url.length > 1)
            {
                //The index start at 0 so -1
                var sectionnum = parseInt(url[1].split('-')[1])-1;
                tabview.selectChild(sectionnum);
            }
        });
        addonload(function()
        {
            document.getElementById("maincontainer").style.display='';
        }); 
    }
}