#!/bin/bash

echo "Starting setup for Linux (Arch-based)..."

# 1. Install Bun (Native install)
if ! command -v bun &> /dev/null; then
    echo "Installing Bun..."
    curl -fsSL https://bun.sh/install | bash
    source ~/.bashrc # Hoặc source ~/.zshrc tùy shell của bạn
else
    echo "Bun is already installed: $(bun --version)"
fi

# 2. Install PHP
if ! command -v php &> /dev/null; then
    echo "Installing PHP via pacman..."
    sudo pacman -S --noconfirm php
else
    echo "PHP is already installed: $(php -v | head -n 1)"
fi

# 3. Test installations
echo "Testing versions..."
bun --version && php -v | head -n 1

# 4. Bun install in client folder
if [ -d "client" ]; then
    echo "Found 'client' folder. Running bun install..."
    cd client && bun install
else
    echo "Folder 'client' not found!"
fi

echo "All done! Ready to scale!"