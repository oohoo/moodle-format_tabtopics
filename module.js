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
            //get highlighted section
            counter = 0;
            thisone=0;
            Y.all('#sections .yui3-tabview-list li').each(function (node)
            {
                if (node.one('#marker'))
                {
                    thisone = counter;
                }
                counter++;
            });
			
            //get the URL param to select the good section by  default
            var url=document.URL.split('#');
            if(url.length > 1)
            {
                //The index start at 0 so -1
                var sectionnum = parseInt(url[1].split('-')[1])-1;
                tabview.selectChild(sectionnum);
            }
            else
            {
                tabview.selectChild(thisone);
            }

        });
        addonload(function()
        {
            document.getElementById("maincontainer").style.display='';
        }); 
    }
}
