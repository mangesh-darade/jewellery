<style>
div#s2id_relation {
    width: 115px;
}
.sortable.sortable {
  cursor: pointer;
}

.sortable.sortable.asc::after {
  content: " \2191"; /* Up arrow */
}

.sortable.sortable.desc::after {
  content: " \2193"; /* Down arrow */
}
div#relation-error{
    position: absolute;
    margin-top: 54px;
    margin-left: 0px;
}
div#name-error {
    position: absolute;
    margin-top: 54px;
    margin-left: 23rem;
}
div#event_type-error{
    position: absolute;
    margin-top: 55px;
    margin-left: 53rem;
}
div#date-error {
    position: absolute;
    margin-top: 57px;
    margin-left: 80rem;
}
.Occasion{
    width:100px;
}
</style>
<script>

</script>
<div class="form-container">
    <label for="relation">Relation:</label>
    <select id="relation" name="relation">
        <option value="">Select</option>
        <?php foreach ($relations as $relation): ?>
        <option value="<?php echo $relation->id; ?>"><?php echo $relation->name; ?></option>
        <?php endforeach; ?>
    </select>
    <div class="error-message" id="relation-error" style="color: red; display: none;">This field is required.</div>
    
    <input type="hidden" id="customer_id" name="customer_name_family">
    <input type="hidden" id="customer_details_id" name="customerDetails">

    <label for="name">Name:</label>
    <input type="text" id="personName" name="name" pattern="^[A-Za-z\s]+$" required>
    <div class="error-message" id="name-error" style="color: red; display: none;">This field is required.</div>

    <label for="event_type">Occasion:</label>
    <select class="Occasion" name="event_type" id="event_type">
        <option value="">Select</option>
        <?php foreach ($events as $event): ?>
        <option value="<?php echo $event->id; ?>"><?php echo $event->name; ?></option>
        <?php endforeach; ?>
    </select>
    <div class="error-message" id="event_type-error" style="color: red; display: none;">This field is required.</div>

    <label for="date">Date:</label>
    <input type="date" id="date" name="date" min="1900-01-01" max="2100-12-31" required>
    <div class="error-message" id="date-error" style="color: red; display: none;">This field is required.</div>

    <button type="button" class="add-button" style="display: none;" id="submitBtn">Add</button>
    <button type="button" class="add-button" style="display: none;" id="editButton">Save</button>
</div>
<div class="table-container" style="margin-top: 15px;">
    <h4>Relations & Occasions</h4>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Relation</th>
                <th>Name</th>
                <th>Occasion</th>
                <th>Date</th>
                <th>Actions</th> <!-- Actions column -->
            </tr>
        </thead>
        <tbody id="gridContent">
            <!-- Dynamically added grid content will appear here -->
        </tbody>
    </table>
</div>
</div>
<script>
document.querySelectorAll(".tab-nav button").forEach(button => {
    button.addEventListener("click", function() {
        document.querySelectorAll(".tab-nav button").forEach(btn => btn.classList.remove("active"));
        this.classList.add("active");
    });
});
document.getElementById("personName").addEventListener("input", function() {
    this.value = this.value.replace(/[^A-Za-z]/g, ''); // Remove non-alphabet characters
});

</script>