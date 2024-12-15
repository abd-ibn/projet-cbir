# from flask import Flask, jsonify, send_file
# import base64
# from io import BytesIO
# from PIL import Image
# import numpy as np

# app = Flask(__name__)

# @app.route('/descriptors/<image_id>')
# def get_descriptors(image_id):
#     # Generate or load your color histogram image as a numpy array
#     # Example: Create a simple color histogram image for demonstration
#     image = np.zeros((100, 100, 3), dtype=np.uint8)  # A black image as a placeholder
#     image[0:50, 0:50] = [255, 0, 0]  # Red color
#     image[50:100, 50:100] = [0, 255, 0]  # Green color

#     # Convert to PIL Image for base64 encoding
#     pil_image = Image.fromarray(image)
#     buffered = BytesIO()
#     pil_image.save(buffered, format="PNG")
#     encoded_image = base64.b64encode(buffered.getvalue()).decode('utf-8')

#     # Construct the response data
#     descriptors = {
#         'color_histogram': encoded_image,
#         'dominant_colors': ['#FF0000', '#00FF00', '#0000FF'],  # Sample data
#         'texture': [0.45, 0.78],  # Example texture descriptor
#         'hu_moments': [1, 0.5]  # Example Hu moments
#     }
#     return jsonify(descriptors)

# if __name__ == '__main__':
#     app.run(host='127.0.0.1', port=5000)


from flask import Flask, jsonify, request
import base64
from io import BytesIO
from PIL import Image
import numpy as np
import cv2
from sklearn.cluster import KMeans

app = Flask(__name__)


def calculer_histogramme(image_path, afficher=False):
    # Charger l'image
    image = cv2.imread(image_path)
    
    if image is None:
        raise FileNotFoundError(f"Impossible de charger l'image à l'emplacement : {image_path}")
    
    # Vérifier si l'image est en couleur ou en niveaux de gris
    if len(image.shape) == 2:  # Image en niveaux de gris
        histogram = cv2.calcHist([image], [0], None, [256], [0, 256]).flatten().tolist()
        return histogram
    
    else:  # Image en couleur (BGR)
        histograms = {}
        couleurs = ('b', 'g', 'r')  # OpenCV utilise l'ordre BGR
        for i, couleur in enumerate(couleurs):
            histograms[couleur] = cv2.calcHist([image], [i], None, [256], [0, 256]).flatten().tolist()
        return histograms


def trouver_couleurs_dominantes(image_path, k=3):
    # Charger l'image
    image = cv2.imread(image_path)
    if image is None:
        return None  # Retourne None si l'image ne peut pas être chargée
    
    # Convertir l'image en RGB (OpenCV charge en BGR par défaut)
    image = cv2.cvtColor(image, cv2.COLOR_BGR2RGB)
    
    # Réduire l'image à un tableau 2D de pixels (ignorer la dimension spatiale)
    pixels = image.reshape((-1, 3))

    # Appliquer k-means clustering
    kmeans = KMeans(n_clusters=k, random_state=42)
    kmeans.fit(pixels)

    # Couleurs dominantes (centres des clusters)
    couleurs_dominantes = kmeans.cluster_centers_.astype(int).tolist()

    return couleurs_dominantes



# Fonction pour créer un noyau Gabor
def create_gabor_kernel(ksize, sigma, theta, lambd, gamma):
    return cv2.getGaborKernel((ksize, ksize), sigma, theta, lambd, gamma, psi=0, ktype=cv2.CV_32F)

# Appliquer les filtres Gabor à une image
def apply_gabor_filters(image, orientations, scales):
    descriptors = []
    for theta in orientations:
        for lambd in scales:
            kernel = create_gabor_kernel(ksize=31, sigma=4.0, theta=theta, lambd=lambd, gamma=0.5)
            filtered_img = cv2.filter2D(image, cv2.CV_8UC3, kernel)
            mean, std_dev = cv2.meanStdDev(filtered_img)
            descriptors.append(mean[0][0])
            descriptors.append(std_dev[0][0])
    return descriptors

# Convert image to base64 encoding
def image_to_base64(img):
    _, buffer = cv2.imencode('.png', img)
    return base64.b64encode(buffer).decode('utf-8')

# Apply filters and return visual base64-encoded images
def get_filter_images(image, orientations, scales):
    filter_images = []
    for theta in orientations:
        for lambd in scales:
            kernel = create_gabor_kernel(ksize=31, sigma=4.0, theta=theta, lambd=lambd, gamma=0.5)
            filtered_img = cv2.filter2D(image, cv2.CV_8UC3, kernel)
            filter_images.append(f"data:image/png;base64,{image_to_base64(filtered_img)}")
    return filter_images



import os
import cv2
import numpy as np
import json
import imutils

def calculer_moments_hu(image_path):
  
    # Charger l'image et la convertir en niveaux de gris
    image = cv2.imread(image_path)
    if image is None:
        return None  # Si l'image ne peut pas être chargée
    
    gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)
    
    # Appliquer un seuillage binaire
    thresh = cv2.threshold(gray, 5, 255, cv2.THRESH_BINARY)[1]
    
    # Trouver les contours et conserver le plus grand
    cnts = cv2.findContours(thresh.copy(), cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)
    cnts = imutils.grab_contours(cnts)
    
    if not cnts:
        return None  # Aucun contour trouvé
    
    c = max(cnts, key=cv2.contourArea)
    
    # Extraire la région d'intérêt (ROI) et la redimensionner
    x, y, w, h = cv2.boundingRect(c)
    roi = cv2.resize(thresh[y:y + h, x:x + w], (50, 50))
    
    # Calculer les moments de Hu
    moments = cv2.HuMoments(cv2.moments(roi)).flatten()
    return moments.tolist()








@app.route('/descriptors', methods=['POST'])
def get_descriptors():
    image_path = request.json.get('image_path')

    try:
        # Read the image (assuming it is in grayscale)
        image = cv2.imread(image_path, cv2.IMREAD_GRAYSCALE)
        if image is None:
            return jsonify({'error': 'Failed to load image'}), 500

        # Apply Gabor filters to extract texture descriptors (keeping your original function)
        orientations = [0, np.pi / 4, np.pi / 2, 3 * np.pi / 4]  # 4 orientations
        scales = [4, 8, 16, 32]  # Different scales
        gabor_descriptors = apply_gabor_filters(image, orientations, scales)

        # Get the visual representation of the filter outputs (base64-encoded images)
        filter_images = get_filter_images(image, orientations, scales)

        # Calculate color histograms and dominant colors (example functions)
        histograms = calculer_histogramme(image_path)
        dominant_colors = trouver_couleurs_dominantes(image_path)
        if dominant_colors is None:
            return jsonify({'error': 'Failed to extract dominant colors'}), 500
        dominant_colors_hex = ['#%02x%02x%02x' % tuple(color) for color in dominant_colors]

        # Calculate Hu moments
        hu_moments = calculer_moments_hu(image_path)  # Get the actual Hu moments

        if hu_moments is None:
            return jsonify({'error': 'Failed to calculate Hu moments'}), 500

        # Construct the response
        descriptors = {
            'color_histograms': histograms,
            'dominant_colors': dominant_colors_hex,
            'texture_images': filter_images,  # Base64-encoded filter images
            'hu_moments': hu_moments,  # Actual Hu moments
            'gabor_descriptors': gabor_descriptors  # Numerical texture descriptors
        }

        return jsonify(descriptors)

    except Exception as e:
        return jsonify({'error': str(e)}), 500









if __name__ == '__main__':
    app.run(host='127.0.0.1', port=5000)
