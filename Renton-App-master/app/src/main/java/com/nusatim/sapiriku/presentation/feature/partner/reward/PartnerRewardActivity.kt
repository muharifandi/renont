package com.nusatim.sapiriku.presentation.feature.partner.reward
import android.os.Bundle
import android.view.MenuItem
import android.widget.Toast
import androidx.activity.viewModels
import androidx.appcompat.app.AppCompatActivity
import androidx.lifecycle.Lifecycle
import androidx.lifecycle.lifecycleScope
import androidx.lifecycle.repeatOnLifecycle
import com.google.android.material.tabs.TabLayout
import com.nusatim.sapiriku.R
import com.nusatim.sapiriku.core.common.UiState
import com.nusatim.sapiriku.databinding.ActivityPartnerRewardBinding
import com.nusatim.sapiriku.domain.model.BasicData
import com.nusatim.sapiriku.domain.model.PartnerReward
import com.nusatim.sapiriku.presentation.feature.partner.reward.adapter.ListPartnerRewardAdapter
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.launch
import com.nusatim.sapiriku.core.util.applyExitTransition

@AndroidEntryPoint
class PartnerRewardActivity : AppCompatActivity() {

    private lateinit var binding: ActivityPartnerRewardBinding
    private val viewModel: PartnerRewardViewModel by viewModels()

    private var scopes: List<BasicData> = emptyList()
    private var scopeIndex = 0

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityPartnerRewardBinding.inflate(layoutInflater)
        setContentView(binding.root)

        supportActionBar?.apply {
            setDisplayHomeAsUpEnabled(true)
            elevation = 0f
        }

        binding.srLayout.setOnRefreshListener { loadCurrentScope() }

        observeState()
        viewModel.loadScopes()
    }

    private fun setupScopeTabs(data: List<BasicData>) {
        scopes = data
        binding.tabScope.removeAllTabs()
        data.forEach { scope ->
            binding.tabScope.addTab(binding.tabScope.newTab().setText(scope.name))
        }

        if (binding.tabScope.tabCount > 0) {
            binding.tabScope.getTabAt(scopeIndex)?.select()
        }

        binding.tabScope.addOnTabSelectedListener(object : TabLayout.OnTabSelectedListener {
            override fun onTabSelected(tab: TabLayout.Tab) {
                scopeIndex = binding.tabScope.selectedTabPosition
                loadCurrentScope()
            }

            override fun onTabUnselected(tab: TabLayout.Tab) = Unit
            override fun onTabReselected(tab: TabLayout.Tab) = Unit
        })

        scopeIndex = binding.tabScope.selectedTabPosition.coerceAtLeast(0)
        loadCurrentScope()
    }

    private fun loadCurrentScope() {
        val scope = scopes.getOrNull(scopeIndex)?.id ?: return
        viewModel.loadRewards(scope)
    }

    private fun bindRewards(rewards: List<PartnerReward>) {
        val adapter = ListPartnerRewardAdapter(this, ArrayList(rewards)) { reward ->
            val scope = scopes.getOrNull(scopeIndex)?.id ?: return@ListPartnerRewardAdapter
            viewModel.claimReward(reward.rewardId, scope)
        }
        binding.list.adapter = adapter
    }

    private fun observeState() {
        lifecycleScope.launch {
            repeatOnLifecycle(Lifecycle.State.STARTED) {
                launch {
                    viewModel.scopes.collect { state ->
                        if (state is UiState.Success) setupScopeTabs(state.data)
                        if (state is UiState.Error) {
                            Toast.makeText(this@PartnerRewardActivity, getString(R.string.failed_check_to_server), Toast.LENGTH_LONG).show()
                        }
                    }
                }
                launch {
                    viewModel.rewards.collect { state ->
                        binding.srLayout.isRefreshing = state is UiState.Loading
                        if (state is UiState.Success) bindRewards(state.data)
                        if (state is UiState.Error) {
                            Toast.makeText(this@PartnerRewardActivity, getString(R.string.failed_check_to_server), Toast.LENGTH_LONG).show()
                        }
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
