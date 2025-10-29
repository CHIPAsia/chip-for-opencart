<img src="./assets/logo.svg" alt="drawing" width="50"/>

# CHIP for OpenCart

This module adds CHIP payment method option to your OpenCart.

## Installation

### Easy Installation (Recommended)

**Download pre-built packages from [Releases](https://github.com/CHIPAsia/chip-for-opencart/releases)**

Ready-to-install `.ocmod.zip` files are automatically generated for each OpenCart version. Simply:
1. Go to the [Releases page](https://github.com/CHIPAsia/chip-for-opencart/releases)
2. Download the zip file for your OpenCart version (e.g., `chip-opencart-4.1.ocmod.zip`)
3. Upload and install via OpenCart Extension Installer

### Manual Installation

If you prefer to build from source, choose your OpenCart version folder:

* [OpenCart 1.5.x](https://download-directory.github.io/?url=https%3A%2F%2Fgithub.com%2FCHIPAsia%2Fchip-for-opencart%2Ftree%2Fmain%2F1.5)
* [OpenCart 2.0.x & 2.1.x](https://download-directory.github.io/?url=https%3A%2F%2Fgithub.com%2FCHIPAsia%2Fchip-for-opencart%2Ftree%2Fmain%2F2.0)
* [OpenCart 2.2.x](https://download-directory.github.io/?url=https%3A%2F%2Fgithub.com%2FCHIPAsia%2Fchip-for-opencart%2Ftree%2Fmain%2F2.2)
* [OpenCart 2.3.x](https://download-directory.github.io/?url=https%3A%2F%2Fgithub.com%2FCHIPAsia%2Fchip-for-opencart%2Ftree%2Fmain%2F2.3)
* [OpenCart 3.0.x](https://download-directory.github.io/?url=https%3A%2F%2Fgithub.com%2FCHIPAsia%2Fchip-for-opencart%2Ftree%2Fmain%2F3.0)
* [OpenCart 4.0.0](https://download-directory.github.io/?url=https%3A%2F%2Fgithub.com%2FCHIPAsia%2Fchip-for-opencart%2Ftree%2Fmain%2F4.0)
* [OpenCart 4.1.x](https://download-directory.github.io/?url=https%3A%2F%2Fgithub.com%2FCHIPAsia%2Fchip-for-opencart%2Ftree%2Fmain%2F4.1)

**Note:** For versions 1.5-3.0, zip the contents of the `upload/` folder. For versions 4.0+, zip the folder contents directly (excluding README.md).

## Configuration

Set the **Brand ID** and **Secret Key** in the plugins settings.

## Development

### Building Packages Locally

To build all zip packages locally, run:

```bash
./build-zips.sh [VERSION]
```

Examples:
```bash
# Build with default version 1.0.0
./build-zips.sh

# Build with specific version
./build-zips.sh 1.2.0
```

This will:
1. Update version numbers in all files (install.json and creator_agent in PHP files)
2. Create all `.ocmod.zip` files in the `dist/` folder

### Automated Releases

GitHub Actions automatically builds and publishes zip packages:

- **On Release/Tag Creation**: Automatically creates all zip files and attaches them to the release
- **Manual Trigger**: Can be triggered manually from the Actions tab
- **Artifacts**: Build artifacts are available for 90 days after each build
- **Version Sync**: Automatically extracts version from the release tag (e.g., `v1.2.0`) and updates all version references in files

### Release Process

1. Make your code changes
2. Create a new release/tag on GitHub (e.g., `v1.2.0`)
   - The version from the tag (without the `v` prefix) will be automatically used
3. GitHub Actions will automatically:
   - Extract the version from the tag (e.g., `v1.2.0` → `1.2.0`)
   - Update all version references in `install.json` and `creator_agent` fields
   - Build zip files for all OpenCart versions
   - Attach them to the release
   - Make them available for download

**Note**: Version numbers are dynamically set from the release tag, so you don't need to manually update version numbers in the code before creating a release.

## Other

Facebook: [Merchants & DEV Community](https://www.facebook.com/groups/3210496372558088)