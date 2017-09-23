<% if SmallSource %>

    <%--
        <!-- DEBUGGING -->
        <img src="$DefaultSource" alt=""><br>
        <img src="$MediumSource" alt=""><br>
        <img src="$LargeSource" alt=""><br>
    --%>
    <%-- $Width | $Height --%>

    <!-- RESPONSIVE IMAGE $ID -->
    <img src="$SmallSource" srcset="$SmallSource $SmallSourceWidth, $MediumSource $MediumSourceWidth, $LargeSource $LargeSourceWidth" <% if MediaQuery %>sizes="$MediaQuery"<% end_if %> <% if $ExtraClasses %>class="$ExtraClasses"<% end_if %>
<% else %>
    <img <% if $ExtraClasses %>class="$ExtraClasses"<% end_if %>
<% end_if %>