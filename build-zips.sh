#!/bin/bash

# Build script to create zip files for all OpenCart versions
# Usage: ./build-zips.sh [VERSION]
# Example: ./build-zips.sh 1.0.0

set -e

VERSION="${1:-1.0.0}"
echo "Building CHIP for OpenCart extension packages (version: $VERSION)..."

# Update version numbers in files
echo "Updating version references to: $VERSION"

# Update install.json files
find . -name "install.json" -type f -not -path "./dist/*" -exec sed -i '' "s/\"version\": \"[^\"]*\"/\"version\": \"$VERSION\"/g" {} +

# Update creator_agent in PHP files
find . -name "*.php" -type f -not -path "./dist/*" -exec perl -i -pe "s/(['\"]creator_agent['\"][\s]*=>[\s]*['\"]OC[0-9]+:\s*)[^'\"]+/\\1$VERSION/g" {} +

# Create dist directory
mkdir -p dist

# Build zips for versions with upload/ folder (1.5 - 3.0)
for version in 1.5 2.0 2.2 2.3 3.0; do
    if [ -d "$version/upload" ]; then
        echo "Building zip for OpenCart $version.x..."
        cd "$version/upload"
        zip -r "../../dist/chip-opencart-$version.ocmod.zip" .
        cd ../..
    fi
done

# Build zips for versions without upload/ folder (4.0+)
for version in 4.0 4.1; do
    if [ -d "$version" ]; then
        echo "Building zip for OpenCart $version.x..."
        cd "$version"
        zip -r "../dist/chip-opencart-$version.ocmod.zip" . -x "README.md"
        cd ..
    fi
done

echo ""
echo "✅ All zip files created successfully in dist/ folder!"
echo ""
ls -lh dist/*.ocmod.zip

