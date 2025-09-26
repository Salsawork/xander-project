import axios from "axios";
window.axios = axios;

window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

window.toggleDropdown = function () {
    const dropdown = document.getElementById("userDropdown");
    console.log(dropdown.classList.contains("hidden"));
    if (dropdown.classList.contains("hidden")) {
        dropdown.classList.remove("hidden");
    } else {
        dropdown.classList.add("hidden");
    }
    console.log("Dropdown toggled");
};

window.onclick = function (event) {
    if (!event.target.matches(".relative *")) {
        document.getElementById("userDropdown").classList.add("hidden");
    }
};

window.ImageData = [];
window.addImage = function (imageUrl) {
    window.ImageData.push(imageUrl);
};

window.removeLastImage = function () {
    window.ImageData.pop();
};

window.uploadFiles = function (event) {
    const files = event.target.files;
    const formData = new FormData();

    for (let i = 0; i < files.length; i++) {
        formData.append("image", files[i]);
    }

    // Tambahkan CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch("/upload", {
        method: "POST",
        headers: {
            'X-CSRF-TOKEN': csrfToken
        },
        body: formData,
    })
        .then((response) => {
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.status);
            }
            return response.json();
        })
        .then((data) => {
            if (data.success) {
                // Hapus domain dari path
                let imagePath = data.data.path;
                // Hapus domain jika ada
                if (imagePath.includes('http://') || imagePath.includes('https://')) {
                    // Ambil path setelah /storage/
                    const parts = imagePath.split('/storage/');
                    if (parts.length > 1) {
                        imagePath = '/storage/' + parts[1];
                    }
                }
                window.addImage(imagePath);
                window.loadImages();
                console.log("Upload berhasil:", imagePath);
            } else {
                console.error("Upload failed:", data.message);
            }
        })
        .catch(error => {
            console.error("Error uploading file:", error);
        });
};

window.loadImages = function () {
    for (let i = 0; i < 4; i++) {
        const imageElement = document.getElementById(
            "product-image-" + (i + 1)
        );
        const inputElement = document.getElementById("input-image-" + (i + 1));
        if (imageElement && inputElement) {
            if (window.ImageData[i]) {
                // Cek apakah path gambar sudah lengkap atau tidak
                let imagePath = window.ImageData[i];
                let displayPath = imagePath;
                
                // Jika path tidak dimulai dengan http:// atau https:// atau /storage/
                if (!imagePath.startsWith('http://') && !imagePath.startsWith('https://') && !imagePath.startsWith('/storage/')) {
                    // Tambahkan path /storage/uploads/ di depannya untuk display
                    displayPath = '/storage/uploads/' + imagePath;
                }
                
                // Set src untuk display dengan path lengkap
                imageElement.src = displayPath;
                imageElement.alt = "Product Image " + (i + 1);
                
                // Tetap simpan nama file asli di input hidden
                inputElement.value = imagePath;
            }
        }
    }
};

window.showCart = function () {
    const cartOverlay = document.getElementById("cart");
    if (cartOverlay.classList.contains("hidden")) {
        cartOverlay.classList.remove("hidden");
    } else {
        cartOverlay.classList.add("hidden");
    }
};

window.handleCheckboxChange = function (checkbox) {
    if (checkbox.checked) {
        checkbox.setAttribute("name", "items[]");
        const priceElement = document.getElementById("price-" + checkbox.value);
        priceElement.setAttribute("name", "price[]");
    } else {
        checkbox.removeAttribute("name");
        const priceElement = document.getElementById("price-" + checkbox.value);
        priceElement.removeAttribute("name");
    }
};
