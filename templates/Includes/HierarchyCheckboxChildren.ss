<% if $children %>
    <div class="child-categories">
        <% loop $children %>
            <div class="checkbox form-check $Class">
                <label class="form-check-label">
                    <input id="$ID" class="checkbox form-check-input" name="$Name" type="checkbox" value="$Value"<% if $isChecked %> checked="checked"<% end_if %><% if $isDisabled %> disabled="disabled"<% end_if %> />
                    $Title
                </label>
            </div>
        <% end_loop %>
    </div>
<% end_if %>