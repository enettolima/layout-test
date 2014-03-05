function showMenu(listObj)
{
	var menuObj = listObj.parentNode;

	if (menuObj == null || menuObj.className != "menu")
	return false;

	for (var i = 0; i < menuObj.childNodes.length; i++)
	{
		var childObj = menuObj.childNodes[i];

		if (childObj.tagName == "LI")
		{
			if (childObj == listObj && childObj.className == "menu-closed")
				childObj.className = "menu-open";
			else if (childObj.className == "menu-open")
				childObj.className = "menu-closed";
		}

	}

	return false;
}