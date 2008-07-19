/*
 * Menu: Editors > Toggle Word Wrap
 * Kudos: Kevin Lindsey (Aptana, Inc.)
 * License: EPL 1.0
 * DOM: http://download.eclipse.org/technology/dash/update/org.eclipse.eclipsemonkey.lang.javascript
 */

/**
 * main
 */
function main()
{
	var textWidget = getTextWidget();
	
	if (textWidget)
	{
		var setting = textWidget.getWordWrap() == false;
		
		textWidget.setWordWrap(setting);
		
		if (setting)
		{
			out.println("Word wrap is on");
		}
		else
		{
			out.println("Word wrap is off");
		}
	}
}

/**
 * getTextWidget
 */
function getTextWidget()
{
	var result = null;
	
	try
	{
		result = editors.activeEditor.textEditor.getViewer().getTextWidget()
	}
	catch (e)
	{
		// fail silently
	}
	
	return result;
}
