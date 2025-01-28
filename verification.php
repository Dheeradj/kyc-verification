<!DOCTYPE html>
<html lang="en">
<head>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KYC Verification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            max-width: 500px;
            width: 100%;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .form-group {
            display: flex;
            flex-direction: column;
        }
        label {
            font-weight: bold;
            margin-bottom: 5px;
        }
        input[type="file"], input[type="submit"] {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        input[type="submit"] {
            background-color: #007bff;
            color: #fff;
            cursor: pointer;
            border: none;
        }
        input[type="submit"]:hover {
            background-color: #0056b3;
        }
        .note {
            font-size: 0.9em;
            color: #666;
            text-align: center;
        }
        video {
            width: 100%;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        .camera-container {
            text-align: center;
        }
        .camera-container button {
            padding: 10px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .camera-container button:hover {
            background-color: #0056b3;
        }
        .preview-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .preview-container div {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .preview-container img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 5px;
        }
        .delete-btn {
            background-color: red;
            color: white;
            padding: 5px;
            cursor: pointer;
            border: none;
            border-radius: 5px;
            margin-top: 5px;
        }
        .logo {
        background-color: #10069f; /* Background color for the logo */
        padding: 10px; /* Padding for spacing around the logo */
        border-radius: 5px; /* Round the corners of the image */
        display: block; /* Makes the image a block-level element */
        margin: 0 auto; /* Centers the image horizontally */
    }

    .kyc-title {
        background-color: #007bff; /* Background color for the heading */
        color: white; /* White text color */
        padding: 20px; /* Padding around the heading */
        text-align: center; /* Center-align the text */
        border-radius: 5px; /* Optional: round the corners of the heading */
    }
    </style>
</head>
<body>
    <div class="container">
    <img src="telesur_logo_2x-1.png" alt="logo" class="logo">
    <h1 class="kyc-title">KYC Verification</h1>
        <form id="kycForm">
            <!-- Camera for Photo 1 -->
            <div class="form-group camera-container">
                <video id="camera" autoplay></video>
                <button type="button" onclick="capturePhoto('photo1')">Capture Photo 1</button>
            </div>
            <input type="hidden" name="photo1" id="photo1">

            <!-- Camera for Photo 2 -->
            <div class="form-group camera-container">
                <button type="button" onclick="capturePhoto('photo2')">Capture Photo 2</button>
            </div>
            <input type="hidden" name="photo2" id="photo2">

            <!-- Upload ID Photo -->
            <div class="form-group">
                <label for="id_photo_file">Upload ID Photo:</label>
                <input type="file" name="id_photo" id="id_photo_file" accept="image/*" onchange="previewIDPhoto()">
            </div>

            <div class="preview-container" id="previewContainer">
                <!-- Preview images will be shown here -->
            </div>

            <input type="submit" value="Submit">
        </form>
        <p class="note">Please ensure the images are clear and meet the requirements.</p>
    </div>

    <script>
        let photoCount = 0;

// Start camera stream
function startCamera() {
    navigator.mediaDevices.getUserMedia({ video: true })
        .then(stream => {
            document.getElementById('camera').srcObject = stream;
        })
        .catch(err => {
            console.error("Error accessing camera: ", err);
        });
}

// Capture photos in sequence
function capturePhoto(inputId) {
    if (photoCount < 2) {
        const video = document.getElementById('camera');
        const canvas = document.createElement('canvas');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
        const dataURL = canvas.toDataURL('image/png');
        document.getElementById(inputId).value = dataURL;  // Set the base64 value to the hidden input
        photoCount++;

        // Add preview for the captured photo
        const previewContainer = document.getElementById('previewContainer');
        const previewWrapper = document.createElement('div');
        const img = document.createElement('img');
        img.src = dataURL;
        const deleteBtn = document.createElement('button');
        deleteBtn.classList.add('delete-btn');
        deleteBtn.innerText = 'Delete';
        deleteBtn.onclick = function() {
            previewWrapper.remove();
            document.getElementById(inputId).value = '';  // Clear hidden input
            photoCount--;
        };

        previewWrapper.appendChild(img);
        previewWrapper.appendChild(deleteBtn);
        previewContainer.appendChild(previewWrapper);
    }
}

// Preview uploaded ID photo with delete button
function previewIDPhoto() {
    const fileInput = document.getElementById('id_photo_file');
    const previewContainer = document.getElementById('previewContainer');

    if (fileInput.files && fileInput.files[0]) {
        const reader = new FileReader();
        reader.onload = function (e) {
            // Add preview for the uploaded ID photo
            const previewWrapper = document.createElement('div');
            const img = document.createElement('img');
            img.src = e.target.result;
            const deleteBtn = document.createElement('button');
            deleteBtn.classList.add('delete-btn');
            deleteBtn.innerText = 'Delete';
            deleteBtn.onclick = function() {
                previewWrapper.remove();
                fileInput.value = ''; // Clear the file input
            };

            previewWrapper.appendChild(img);
            previewWrapper.appendChild(deleteBtn);
            previewContainer.appendChild(previewWrapper);
        };
        reader.readAsDataURL(fileInput.files[0]);
    }
}

// Handle form submission with FormData
document.getElementById('kycForm').addEventListener('submit', function (event) {
    event.preventDefault();

    const photo1 = document.getElementById('photo1').value;
    const photo2 = document.getElementById('photo2').value;
    const idPhoto = document.getElementById('id_photo_file').files[0];

    if (!photo1 || !photo2 || !idPhoto) {
        Swal.fire({
            title: "Missing Photos",
            text: "Please capture both photos and upload the ID photo.",
            icon: "warning",
        });
        return;
    }

    const reader = new FileReader();
    reader.onloadend = function () {
        const data = {
            photo1: photo1,
            photo2: photo2,
            id_photo: reader.result.split(',')[1],
        };

        fetch('upload_kyc.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data),
        })
        .then(response => {
            if (!response.ok) {
                console.error('HTTP Error:', response.status, response.statusText);
                throw new Error('Server responded with an error');
            }
            return response.json();
        })
        .then(data => {
        console.log('Response Data:', data);

        // Access verificationLevel correctly
        const verificationLevel = data.data ? data.data.verificationLevel : null;

        if (verificationLevel === "LEVEL_5") {
            Swal.fire({
                title: "User Verified!",
                text: "Verification Level: " + verificationLevel,
                icon: "success",
            });
        } else {
            Swal.fire({
                title: "Verification Failed",
                text: "Verification Level: " + verificationLevel,
                icon: "error",
            });
        }
    })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                title: "Error",
                text: "An error occurred during verification. Please try again.",
                icon: "error",
            });
            console.log('Error details:', error);
        });
    };
    reader.readAsDataURL(idPhoto);
});
startCamera();
    </script>
</body>
</html>
