# MLP 2 to MLP 5 Migration Tool  
  
[![WordPress](https://img.shields.io/badge/WordPress-Multisite-blue.svg)](https://wordpress.org/)  
[![PHP](https://img.shields.io/badge/PHP-8.0%2B-purple.svg)](https://php.net/)  
  
A WordPress plugin that facilitates the migration of site relationships and content relationships from MultilingualPress version 2 to MultilingualPress version 5.  
  
## 🚀 Features  
  
- ✅ **Automatic Version Detection** - Detects whether MLP 2 or MLP 5 is active  
- 📤 **Export Functionality** - Exports all site and content relationships from MLP 2 to JSON  
- 📥 **Import Functionality** - Imports relationships into MLP 5 using its modern API  
- 🔓 **Legacy Compatibility Bypass** - Automatically bypasses MLP 5's legacy version check  
- 🧹 **Automatic Cleanup** - Removes legacy database options after successful migration  
- 🌐 **Network Admin Integration** - Accessible from WordPress Network Admin dashboard  
  
## 📋 Requirements  
  
- WordPress Multisite installation  
- Network admin access  
- MLP 2 or MLP 5 installed (but not both simultaneously during normal operation)  
- PHP 8.0+ (for MLP 5 compatibility)  
  
## 📦 Installation  
  
1. Download the plugin file (`mlp2-5`)  
2. Upload it to your WordPress plugins directory: `/wp-content/plugins/mlp2-5/`  
3. **Network activate** the plugin from Network Admin → Plugins  
  
## 🔧 Usage  
  
### Step 1: Export from MLP 2  
  
1. Ensure **MLP 2 is active** on your WordPress network  
2. Navigate to **Network Admin → MLP Migration**  
3. Click **"Export Relationships"** to download a JSON file containing:  
   - All site relationships from the `mlp_site_relations` table  
   - All content relationships (posts, terms) from the `multilingual_linked` table  
4. Save the downloaded JSON file (named `mlp-migration-YYYY-MM-DD-HHMMSS.json`)  
  
### Step 2: Switch to MLP 5  
  
1. **Deactivate MLP 2** from Network Admin → Plugins  
2. **Activate MLP 5** from Network Admin → Plugins  
   - The migration tool automatically bypasses MLP 5's legacy version check  
   - You'll see a warning notice prompting you to complete the migration  
  
### Step 3: Import to MLP 5  
  
1. Return to **Network Admin → MLP Migration**  
2. Click **"Choose File"** and select the JSON file you exported in Step 1  
3. Click **"Import Relationships"**  
4. The tool will:  
   - Create all site relationships using MLP 5's `SiteRelations` API  
   - Create all content relationships using MLP 5's `ContentRelations` API  
   - Automatically delete the legacy `inpsyde_multilingual` database option  
5. You'll see a success message showing the number of relationships created  
  
### Step 4: Verify and Clean Up  
  
1. Verify that all your site relationships are intact in **Network Admin → Sites → MultilingualPress**  
2. Check that content relationships are preserved by viewing translated posts  
3. Once verified, you can **deactivate the migration tool**  
  
## 📊 What Gets Migrated  
  
### Site Relationships  
- Connections between sites in your WordPress network  
- Stored in MLP 2's `mlp_site_relations` table  
  
### Content Relationships  
- Post-to-post translations across sites  
- Term-to-term translations (categories, tags)  
- Stored in MLP 2's `multilingual_linked` table  
  
## 🔄 Data Structure Conversion  
  
The tool handles the conversion between MLP 2's flat pairwise relationship structure and MLP 5's relationship ID-based system:  
  
- **MLP 2**: Stores relationships as pairs (Site A ↔ Site B, Post 1 ↔ Post 2)  
- **MLP 5**: Groups related content under a single relationship ID  
  
## 🐛 Troubleshooting  
  
### "Neither MLP 2 nor MLP 5 is detected"  
- Ensure one of the MLP plugins is network-activated  
- The migration tool detects versions after the `plugins_loaded` hook  
  
### "Failed to parse JSON"  
- Re-export the file from MLP 2  
- Ensure the file wasn't modified or corrupted during download  
- Check that the file is valid JSON  
  
### "MLP 5 services are not available"  
- Ensure MLP 5 is properly activated  
- Try deactivating and reactivating MLP 5  
- Check for PHP errors in your error log  
  
### Import shows 0 relationships created  
- Verify the JSON file contains data (open it in a text editor)  
- Check that the site IDs in the export still exist in your network  
- Ensure content IDs (posts, terms) haven't been deleted  
  
## 📚 Support  
  
For issues related to:  
- **MLP 2**: Refer to the legacy MultilingualPress 2 documentation  
- **MLP 5**: Visit [MultilingualPress documentation](https://multilingualpress.org/docs/)  
- **Migration Tool**: Check the plugin code for inline documentation  
  
## ⚠️ Important Notes  
  
- The migration tool is designed for **one-time use** during the MLP 2 to MLP 5 upgrade process  
- After successful migration, you can safely deactivate and delete the plugin  
- The tool does **not** migrate:  
  - Language settings (these need to be reconfigured in MLP 5)  
  - Module-specific settings (WooCommerce, ACF, etc.)  
  - Custom flags or language names  
- Always **backup your database** before performing the migration  
 
