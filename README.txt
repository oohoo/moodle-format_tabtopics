--------------------------------------------------------------------------------
-------------------------- OOHOO COURSE FORMAT TABTOPICS -----------------------
--------------------------------------------------------------------------------

--------------------------------------------------------------------------------
Description
--------------------------------------------------------------------------------

This course format displays each sections in a tab.

--------------------------------------------------------------------------------
Prerequisites
--------------------------------------------------------------------------------

None

--------------------------------------------------------------------------------
Installation
--------------------------------------------------------------------------------

1.	copy the folder renamed "tabtopics" into the moodle/course/format folder.
2.	Login to Moodle with admin rights. 
3.	In the Site Administration block, click on Notifications a new 
        notification for the installation of Calendar Format will shows
4.      Click on Install


Your done. Go into a course edit page and select Format => Tabtopics format

Note: You can change the tab color by adding this to your theme CSS file

################################################################################
    /* The border under tabs */
    .yui3-skin-sam .yui3-tabview-list
    {
        border-bottom-color: #2647A0 !important;
    }
    /*The regular tab style*/
    .yui3-skin-sam .yui3-tab-label
    {
        background-color: #EFEFEF !important;
    }
    /*The regular tab style hover*/
    .yui3-skin-sam .yui3-tab-label:focus,
    .yui3-skin-sam .yui3-tab-label:hover
    {
        background-color: #EDF5FF !important;
    }
    /*The selected tab border color*/
    .yui3-skin-sam .yui3-tab-selected .yui3-tab-label, 
    .yui3-skin-sam .yui3-tab-selected .yui3-tab-label:focus, 
    .yui3-skin-sam .yui3-tab-selected .yui3-tab-label:hover
    {
        border-color: #243356 !important;
        background-color: #2647A0 !important;
        /* Note: On IE9 you will probably have to put "background: #2647A0 !important;" in order to force the color changes */
    }
    /*The tab content background color*/
    .yui3-skin-sam .yui3-tabview-panel
    {
        background-color: #EDF5FF !important;
    }
################################################################################

--------------------------------------------------------------------------------

For more informations, please go to the online documentation => http://oohoo.biz

