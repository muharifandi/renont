package com.nusatim.sapiriku.presentation.feature.partner.rentvehicle.listpromote
import android.app.Activity
import android.content.Intent
import android.os.Bundle
import android.view.Menu
import android.view.MenuItem
import android.widget.Toast
import androidx.activity.result.contract.ActivityResultContracts
import androidx.activity.viewModels
import androidx.appcompat.app.AlertDialog
import androidx.appcompat.app.AppCompatActivity
import androidx.core.view.isVisible
import androidx.lifecycle.Lifecycle
import androidx.lifecycle.lifecycleScope
import androidx.lifecycle.repeatOnLifecycle
import androidx.recyclerview.widget.LinearLayoutManager
import com.nusatim.sapiriku.R
import com.nusatim.sapiriku.domain.model.OperationResult
import com.nusatim.sapiriku.core.common.UiState
import com.nusatim.sapiriku.custom.PaginationListener
import com.nusatim.sapiriku.databinding.ActivityPartnerListPromoteRentVehicleBinding
import com.nusatim.sapiriku.presentation.feature.partner.rentvehicle.listpromote.adapter.PartnerListPromoteVehicleAdapter
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.launch
import com.nusatim.sapiriku.core.util.applyExitTransition
import com.nusatim.sapiriku.presentation.feature.partner.rentvehicle.addpromote.PartnerAddPromoteRentVehicleActivity
import com.nusatim.sapiriku.presentation.feature.partner.rentvehicle.config.PartnerRentVehicleConfigActivity

@AndroidEntryPoint
class PartnerListPromoteRentVehicleActivity : AppCompatActivity() {

    private lateinit var binding: ActivityPartnerListPromoteRentVehicleBinding
    private val viewModel: PartnerListPromoteRentVehicleViewModel by viewModels()
    private lateinit var adapter: PartnerListPromoteVehicleAdapter

    private val addPromoteLauncher = registerForActivityResult(
        ActivityResultContracts.StartActivityForResult()
    ) { result ->
        if (result.resultCode == Activity.RESULT_OK) viewModel.loadFirstPage()
    }

    private val configLauncher = registerForActivityResult(
        ActivityResultContracts.StartActivityForResult()
    ) { result ->
        if (result.resultCode == Activity.RESULT_OK) viewModel.loadFirstPage()
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityPartnerListPromoteRentVehicleBinding.inflate(layoutInflater)
        setContentView(binding.root)

        supportActionBar?.apply {
            setDisplayHomeAsUpEnabled(true)
            elevation = 0f
        }

        adapter = PartnerListPromoteVehicleAdapter { promoteVehicle ->
            AlertDialog.Builder(this)
                .setTitle(getString(R.string.cancel_promote))
                .setMessage(getString(R.string.message_cancel_promote, promoteVehicle.title))
                .setNegativeButton(R.string.no, null)
                .setPositiveButton(R.string.yes) { _, _ -> viewModel.cancelPromote(promoteVehicle.id) }
                .show()
        }

        val layoutManager = LinearLayoutManager(this)
        binding.list.layoutManager = layoutManager
        binding.list.adapter = adapter
        binding.list.addOnScrollListener(object : PaginationListener(layoutManager) {
            override fun loadMoreItems() = viewModel.loadMore()
            override fun isLastPage() = viewModel.isLastPage
            override fun isLoading() = viewModel.isLoadingMore
        })
        binding.srLayout.setOnRefreshListener { viewModel.loadFirstPage(isRefresh = true) }

        observeState()
        viewModel.loadFirstPage()
    }

    private fun observeState() {
        lifecycleScope.launch {
            repeatOnLifecycle(Lifecycle.State.STARTED) {
                launch {
                    viewModel.uiState.collect { state ->
                        binding.srLayout.isRefreshing = state.isRefreshing
                        adapter.setLoading(state.isLoadingMore)
                        adapter.submitList(state.items)

                        if (state.isInitialLoading) {
                            binding.shimmer.isVisible = true
                            binding.shimmer.startShimmer()
                        } else {
                            binding.shimmer.stopShimmer()
                            binding.shimmer.isVisible = false
                        }

                        val isEmpty = state.items.isEmpty() && !state.isInitialLoading
                        binding.list.isVisible = !isEmpty
                        binding.txtListMessage.isVisible = isEmpty

                        if (state.error != null) {
                            Toast.makeText(this@PartnerListPromoteRentVehicleActivity, getString(R.string.failed_check_to_server), Toast.LENGTH_LONG).show()
                        }
                    }
                }
                launch {
                    viewModel.cancelState.collect { state -> handleCancelState(state) }
                }
            }
        }
    }

    private fun handleCancelState(state: UiState<OperationResult>) {
        when (state) {
            is UiState.Success -> {
                AlertDialog.Builder(this)
                    .setTitle(getString(R.string.cancel_promote))
                    .setMessage(state.data.message)
                    .setPositiveButton(R.string.yes) { _, _ -> viewModel.loadFirstPage() }
                    .show()
            }
            is UiState.Error -> {
                Toast.makeText(this, getString(R.string.failed_check_to_server), Toast.LENGTH_LONG).show()
            }
            else -> Unit
        }
    }

    override fun onCreateOptionsMenu(menu: Menu): Boolean {
        menuInflater.inflate(R.menu.basic_list, menu)
        menu.findItem(R.id.action_config).isVisible = false
        return super.onCreateOptionsMenu(menu)
    }

    override fun onOptionsItemSelected(item: MenuItem): Boolean {
        when (item.itemId) {
            android.R.id.home -> onBackPressedDispatcher.onBackPressed()
            R.id.action_add -> {
                val intent = Intent(this, PartnerAddPromoteRentVehicleActivity::class.java)
                addPromoteLauncher.launch(intent)
                applyExitTransition(R.anim.slide_in_right, R.anim.slide_out_left)
            }
            R.id.action_config -> {
                val intent = Intent(this, PartnerRentVehicleConfigActivity::class.java)
                configLauncher.launch(intent)
                applyExitTransition(R.anim.slide_in_right, R.anim.slide_out_left)
            }
        }
        return true
    }

    override fun finish() {
        super.finish()
        applyExitTransition(R.anim.slide_in_left, R.anim.slide_out_right)
    }
}
