function handleSearchInput(selectOptions) {
  const searchInput = document.getElementById("patents__searchInput");
  const searchOptions = document.getElementById("patents__searchOptions");

  searchInput.addEventListener("input", function() {
    const searchValue = searchInput.value.toLowerCase();
    searchOptions.innerHTML = "";

    let count = 0;
    for (let i = 0; i < selectOptions.length; i++) {
      const option = selectOptions[i].toLowerCase();
      if (option.includes(searchValue)) {
        const optionElement = document.createElement("option");
        optionElement.value = selectOptions[i];
        optionElement.text = selectOptions[i];
        searchOptions.appendChild(optionElement);
        count++;

        if (count === 5) {
          break;
        }
      }
    }
  });

  // Add event listener for click event
  searchInput.addEventListener("click", function() {
    searchOptions.innerHTML = "";
  });

  // Check if query matches the search input value
  if (query == searchInput.value) {
    searchOptions.innerHTML = "";
  }
}