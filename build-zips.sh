#!/bin/bash

# Build script to create zip files for all OpenCart versions
# Usage: ./build-zips.sh [VERSION]
# Example: ./build-zips.sh 1.0.0

set -e

VERSION="${1:-1.0.0}"
echo "Building CHIP for OpenCart extension packages (version: $VERSION for folder structure only)..."

# Create dist directory structure
mkdir -p "dist/v${VERSION}"

# Build zips for versions with upload/ folder (1.5 - 3.0)
for version in 1.5 2.0 2.2 2.3 3.0; do
    if [ -d "$version/upload" ]; then
        echo "Building zip for OpenCart $version.x..."
        mkdir -p "dist/v${VERSION}/opencart-${version}"
        cd "$version/upload"
        zip -r "../../dist/v${VERSION}/opencart-${version}/chip.ocmod.zip" .
        cd ../..
    fi
done

# Build zips for versions without upload/ folder (4.0+)
for version in 4.0 4.1; do
    if [ -d "$version" ]; then
        echo "Building zip for OpenCart $version.x..."
        mkdir -p "dist/v${VERSION}/opencart-${version}"
        cd "$version"
        zip -r "../dist/v${VERSION}/opencart-${version}/chip.ocmod.zip" . -x "README.md"
        cd ..
    fi
done

# Create master zip file
echo ""
echo "Creating master zip file..."
cd dist
zip -r "chip-for-opencart-v${VERSION}.zip" "v${VERSION}"
cd ..

echo ""
echo "✅ All zip files created successfully!"
echo ""
echo "Master zip: dist/chip-for-opencart-v${VERSION}.zip"
echo ""
echo "Individual zip files:"
find "dist/v${VERSION}" -name "chip.ocmod.zip" -exec ls -lh {} \;

