package com.rentone.user.presentation.feature.account.historybalance
import android.app.Activity
import android.content.Intent
import android.os.Bundle
import android.view.MenuItem
import androidx.activity.result.contract.ActivityResultContracts
import androidx.activity.viewModels
import androidx.appcompat.app.AppCompatActivity
import androidx.fragment.app.Fragment
import androidx.lifecycle.Lifecycle
import androidx.lifecycle.lifecycleScope
import androidx.lifecycle.repeatOnLifecycle
import androidx.viewpager2.adapter.FragmentStateAdapter
import com.google.android.material.tabs.TabLayoutMediator
import com.rentone.user.R
import com.rentone.user.core.common.UiState
import com.rentone.user.core.util.ViewUtils
import com.rentone.user.databinding.ActivityCustomerHistoryBalanceBinding
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.launch
import com.rentone.user.core.util.applyExitTransition
import com.rentone.user.presentation.feature.account.requesttopup.CustomerRequestTopupActivity
import com.rentone.user.presentation.feature.account.requestwithdraw.CustomerRequestWithdrawActivity

@AndroidEntryPoint
class CustomerHistoryBalanceActivity : AppCompatActivity() {

    private lateinit var binding: ActivityCustomerHistoryBalanceBinding
    private val viewModel: CustomerHistoryBalanceViewModel by viewModels()

    private val activityResultLauncher = registerForActivityResult(
        ActivityResultContracts.StartActivityForResult()
    ) { result ->
        if (result.resultCode == Activity.RESULT_OK) {
            viewModel.loadBalance()
        }
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityCustomerHistoryBalanceBinding.inflate(layoutInflater)
        setContentView(binding.root)

        supportActionBar?.setDisplayHomeAsUpEnabled(true)

        setupPager()
        setupListeners()
        observeState()
        viewModel.loadBalance()
    }

    private fun setupPager() {
        binding.vpFragment.adapter = object : FragmentStateAdapter(this) {
            override fun getItemCount() = 2
            override fun createFragment(position: Int): Fragment = when (position) {
                0 -> CustomerTopupFragment()
                else -> CustomerWithdrawFragment()
            }
        }

        TabLayoutMediator(binding.tabMenu, binding.vpFragment) { tab, position ->
            tab.text = if (position == 0) getString(R.string.topup) else getString(R.string.withdraw)
        }.attach()
    }

    private fun setupListeners() {
        binding.btnTopup.setOnClickListener {
            val intent = Intent(this, CustomerRequestTopupActivity::class.java)
            activityResultLauncher.launch(intent)
            applyExitTransition(R.anim.slide_in_right, R.anim.slide_out_left)
        }

        binding.btnWIthdraw.setOnClickListener {
            val intent = Intent(this, CustomerRequestWithdrawActivity::class.java)
            activityResultLauncher.launch(intent)
            applyExitTransition(R.anim.slide_in_right, R.anim.slide_out_left)
        }
    }

    private fun observeState() {
        lifecycleScope.launch {
            repeatOnLifecycle(Lifecycle.State.STARTED) {
                viewModel.balance.collect { state ->
                    if (state is UiState.Success) {
                        binding.txtBalance.text = "Rp. ${ViewUtils.formatCurrency(state.data)},-"
                    }
                }
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
