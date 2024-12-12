import cv2
import sys

# Load images from command-line arguments
images = [cv2.imread(sys.argv[1]), cv2.imread(sys.argv[2]), cv2.imread(sys.argv[3]), cv2.imread(sys.argv[4])]

# Create a stitcher object
stitcher = cv2.Stitcher_create()
stitcher.setPanoConfidenceThresh(float(sys.argv[5]))

# Perform stitching
status, stitched = stitcher.stitch(images)

if status == cv2.Stitcher_OK:
    cv2.imwrite(sys.argv[6], stitched)
else:
    print(status, file=sys.stderr)
    sys.exit(1)
