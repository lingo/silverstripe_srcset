<% if DefaultSource %>

	<%--
		<!-- DEBUGGING -->
		<img src="$DefaultSource" alt=""><br>
		<img src="$MediumSource" alt=""><br>
		<img src="$LargeSource" alt=""><br>
	--%>
	<%-- $Width | $Height --%>

	<!-- RESPONSIVE IMAGE $ID -->
	<img src="$DefaultSource" srcset="$DefaultSource $DefaultSourceWidth, $MediumSource $MediumSourceWidth, $LargeSource $LargeSourceWidth" <% if MediaQuery %>sizes="$MediaQuery"<% end_if %> width="$Width" height="$Height" alt="<% if MenuTitle %>$MenuTitle<% else %>$Title<% end_if %>">
<% else %>
	<img title="Missing image $Title">
<% end_if %>