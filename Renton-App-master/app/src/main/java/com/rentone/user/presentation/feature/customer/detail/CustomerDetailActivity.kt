package com.rentone.user.presentation.feature.customer.detail
import android.content.Intent
import android.os.Bundle
import android.view.MenuItem
import androidx.appcompat.app.AppCompatActivity
import coil.load
import com.rentone.user.R
import com.rentone.user.presentation.feature.common.zoomimage.ZoomImageListActivity
import com.rentone.user.core.common.Config
import com.rentone.user.databinding.ActivityCustomerDetailBinding
import dagger.hilt.android.AndroidEntryPoint
import com.rentone.user.core.util.applyExitTransition

@AndroidEntryPoint
class CustomerDetailActivity : AppCompatActivity() {

    private lateinit var binding: ActivityCustomerDetailBinding

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityCustomerDetailBinding.inflate(layoutInflater)
        setContentView(binding.root)

        supportActionBar?.apply {
            setDisplayHomeAsUpEnabled(true)
            elevation = 0f
        }

        setData()
    }

    private fun setData() {
        val firstName = intent.getStringExtra("first_name")
        val lastName = intent.getStringExtra("last_name")
        val imgProfile = intent.getStringExtra("img_profile")
        val imgIdentity = intent.getStringExtra("img_identity")

        binding.txtName.text = "$firstName $lastName"
        binding.txtIdentityNumber.text = intent.getStringExtra("identity_number")
        binding.txtPhone.text = intent.getStringExtra("phone")

        binding.imageProfile.load(Config.BASE_CUSTOMER_IMAGE + "thumb_" + imgProfile) {
            placeholder(R.drawable.user_image)
            error(R.drawable.user_image)
        }

        binding.imgIdentity.load(Config.BASE_CUSTOMER_IDENTITY_IMAGE + imgIdentity) {
            placeholder(R.drawable.no_image)
            error(R.drawable.no_image)
        }

        if (imgIdentity != null) {
            binding.imgIdentity.setOnClickListener {
                val zoomIntent = Intent(this, ZoomImageListActivity::class.java)
                zoomIntent.putExtra("photo", Config.BASE_CUSTOMER_IDENTITY_IMAGE + imgIdentity)
                startActivity(zoomIntent)
                applyExitTransition(R.anim.slide_in_right, R.anim.slide_out_left)
            }
        }
    }

    override fun onOptionsItemSelected(item: MenuItem): Boolean {
        if (item.itemId == android.R.id.home) {
            onBackPressedDispatcher.onBackPressed()
        }
        return true
    }

    override fun finish() {
        super.finish()
        applyExitTransition(R.anim.slide_in_left, R.anim.slide_out_right)
    }
}
