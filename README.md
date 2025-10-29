<img src="./assets/logo.svg" alt="drawing" width="50"/>

# CHIP for OpenCart

This module adds CHIP payment method option to your OpenCart.

## Installation

### Easy Installation (Recommended)

**Download pre-built packages from [Releases](https://github.com/CHIPAsia/chip-for-opencart/releases)**

Ready-to-install packages are automatically generated for each OpenCart version. Simply:
1. Go to the [Releases page](https://github.com/CHIPAsia/chip-for-opencart/releases)
2. Download the master zip file (e.g., `chip-for-opencart-v1.0.0.zip`)
3. Extract the zip file
4. Navigate to your OpenCart version folder (e.g., `v1.0.0/opencart-4.1/`)
5. Upload `chip.ocmod.zip` via OpenCart Extension Installer

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
1. Create `chip.ocmod.zip` files organized by version and OpenCart version folders
2. Create a master zip file containing all versions: `chip-for-opencart-v{VERSION}.zip`

**Note:** The VERSION parameter is only used for folder naming. It does not modify version numbers in source files.

**Output Structure:**
```
dist/
└── v{VERSION}/
    ├── opencart-1.5/
    │   └── chip.ocmod.zip
    ├── opencart-2.0/
    │   └── chip.ocmod.zip
    ├── opencart-2.2/
    │   └── chip.ocmod.zip
    ├── opencart-2.3/
    │   └── chip.ocmod.zip
    ├── opencart-3.0/
    │   └── chip.ocmod.zip
    ├── opencart-4.0/
    │   └── chip.ocmod.zip
    └── opencart-4.1/
        └── chip.ocmod.zip
```

### Automated Releases

GitHub Actions automatically builds and publishes zip packages:

- **On Release/Tag Creation**: Automatically creates all zip files and attaches them to the release
- **Manual Trigger**: Can be triggered manually from the Actions tab
- **Artifacts**: Build artifacts are available for 90 days after each build
- **Version-based Organization**: Extracts version from the release tag (e.g., `v1.2.0`) for folder organization

### Release Process

1. Make your code changes and update version numbers manually in files if needed
2. Create a new release/tag on GitHub (e.g., `v1.2.0`)
   - The version from the tag (without the `v` prefix) will be used for folder organization
3. GitHub Actions will automatically:
   - Extract the version from the tag (e.g., `v1.2.0` → `1.2.0`)
   - Build `chip.ocmod.zip` files for all OpenCart versions, organized in version folders
   - Create a master zip file: `chip-for-opencart-v1.2.0.zip`
   - Attach the master zip to the release
   - Make it available for download

**Note**: The version tag is only used for folder organization. Source files are not modified during the build process.

## Other

Facebook: [Merchants & DEV Community](https://www.facebook.com/groups/3210496372558088)