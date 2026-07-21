package com.nusatim.sapiriku.presentation.feature.partner.rentvehicle.transaction
import android.os.Bundle
import android.view.MenuItem
import androidx.appcompat.app.AppCompatActivity
import androidx.fragment.app.Fragment
import androidx.viewpager2.adapter.FragmentStateAdapter
import com.google.android.material.tabs.TabLayoutMediator
import com.nusatim.sapiriku.R
import com.nusatim.sapiriku.databinding.ActivityPartnerTransactionBinding
import dagger.hilt.android.AndroidEntryPoint
import com.nusatim.sapiriku.core.util.applyExitTransition

@AndroidEntryPoint
class PartnerTransactionActivity : AppCompatActivity() {

    private lateinit var binding: ActivityPartnerTransactionBinding

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityPartnerTransactionBinding.inflate(layoutInflater)
        setContentView(binding.root)

        supportActionBar?.apply {
            setDisplayHomeAsUpEnabled(true)
            elevation = 0f
        }

        binding.vpFragment.adapter = object : FragmentStateAdapter(this) {
            override fun getItemCount() = 1
            override fun createFragment(position: Int): Fragment = PartnerRentVehicleTransactionFragment()
        }

        TabLayoutMediator(binding.tabMenu, binding.vpFragment) { tab, _ ->
            tab.text = getString(R.string.rent_vehicle)
        }.attach()
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
