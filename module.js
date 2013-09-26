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
 * @author      Dustin Durand                                         **
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later  **
 * *************************************************************************
 * ************************************************************************ */
M.tabtopics =
        {
            init: function(Y, courseid, isZeroTab)
            {
                Y.use('tabview', function(Y)
                {
                    //cookie name for tab state
                    var state_cookie = "moodle_course_format_tabtopics_course_";
                    
                    var tabview = new Y.TabView(
                            {
                                srcNode: '#sections'
                            });
                    //Display the tabs after rendered
                    tabview.after('render', function(e)
                    {
                        Y.one('#maincontainer').setStyle('display', null);
                    });

                    //save cookie state for course
                    tabview.after('selectionChange', function(event) {

                        //do not call on init changes (when loading)
                        //saves the current selected tab to a cookie
                        //
                        if (tabview.enableState === true) {
                            var target_tab = event.newVal;
                            var tab_index = target_tab.get('index');

                            //create date object for one hour for now
                            var expires = new Date();
                            expires.setTime((expires.getTime() + 60*60*1000));//keep cookie for 1 hours
                            
                            //create cookie for current tab selection for one hour
                            Y.Cookie.set(state_cookie + courseid, tab_index, {expires: expires});

                        }
                    });

                    tabview.render();
                    //get highlighted section
                    counter = 0;
                    thisone = 0;
                    Y.all('#sections .yui3-tabview-list li').each(function(node)
                    {
                        if (node.one('#marker'))
                        {
                            thisone = counter;
                        }
                        counter++;
                    });
                    
                    //number of tabs
                    var tab_count = Y.all('#sections .yui3-tab')._nodes.length;

                    //tab selection priority:
                    //URL Identifier > Cookie Setting > Teacher Set Default (marker)
                    //
                    //get the URL param to select the good section by  default
                    var url = document.URL.split('#');
                    if (url.length > 1 && (parseInt(url[1].split('-')[1]) - 1) < tab_count)
                    {
                        //The index start at 0 so -1
                        var sectionnum = parseInt(url[1].split('-')[1]) - 1;
                        tabview.selectChild(sectionnum);
                        
                    }
                    else
                    {
                        //check for saved cookie state for course
                        var value = Y.Cookie.get(state_cookie + courseid);
                        if (is_remember_last_tab_session && value && value < tab_count)
                            tabview.selectChild(value);

                        else {//based on php marker (from default marker setting)
                            tabview.selectChild(thisone);
                            
                        }
                    }
                    
                    

                   
                   //navigation hooks
                   //allows left pane navigation to control tabs
                    Y.all('p[id^=expandable_branch_]').each(function(node)
                    {
                       
                       
                       node.on("click", function (e) {
                      
                       //grab navigation element id (which contain position)
                       var id = node.get("id"); 
                       var tab = id.replace('expandable_branch_', ''); //remove starting of id
                         
                           //if section 0 is not a tab, we need to compensate for it not being present
                           if(!isZeroTab) {
                              if(tab === 1) return;//general pressed, we can ignore
                              tab -= 1; //need to ignore the general tab (section 0)
                           }
                           
                           
                           tab -= 1;//account for 1 based to 0 based conversion
                           tabview.selectChild(tab);//switch tabs
                        });
                       
                    });

                    //allow tab state to be saved now that its completely loaded
                    tabview.enableState = true;

                });
            }
        }
