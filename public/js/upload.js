 document.getElementById("file").addEventListener("change", function () {
        const fileList = this.files;
        const fileListContainer = document.getElementById("fileList");

        fileListContainer.innerHTML = '';


        if (fileList.length === 0) {
            fileListContainer.innerHTML = "<p class='text-muted'>Ningún archivo seleccionado</p>";
            return;
        }


        Array.from(fileList).forEach(file => {
            const fileWrapper = document.createElement("div");
            fileWrapper.classList.add("file-item", "text-center", "mr-3", "mb-3");


            const fileIcon = document.createElement("i");
            fileIcon.classList.add("fas", "fa-file-alt", "fa-2x", "text-primary");

            const fileName = document.createElement("p");
            fileName.classList.add("mt-2", "text-muted", "small", "text-truncate");
            fileName.style.maxWidth = "150px";
            fileName.textContent = file.name;


            fileWrapper.appendChild(fileIcon);
            fileWrapper.appendChild(fileName);


            fileListContainer.appendChild(fileWrapper);
        });
    });


