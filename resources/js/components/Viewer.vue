<template>
  <div class="container">
    <div class="row">
      <div class="col-md-8 offset-md-2">
        <button class="btn btn-success" @click="joinBroadcast">
          Join Stream</button
        ><br />

        <video autoplay ref="viewer"></video>
      </div>
    </div>
  </div>
</template>

<script>
import Peer from "simple-peer";
export default {
  name: "Viewer",
  props: [
    "auth_user_id",
    "stream_id",
    "turn_url",
    "turn_username",
    "turn_credential",
  ],
  data() {
    return {
      streamingPresenceChannel: null,
    };
  },

  methods: {
    joinBroadcast() {
      this.initializeStreamingChannel();
    },

    initializeStreamingChannel() {
      this.streamingPresenceChannel = window.Echo.join(
        `streaming-channel.${this.stream_id}`
      );

      this.streamingPresenceChannel.leaving((user) => {
        console.log("Leaving: ", user);
      });

      this.streamingPresenceChannel.listen("StreamOffer", ({ data }) => {
        console.log("offer data: ", data);
        // check whether you are the intended receipient of the offer
        if (data.receiver.id === this.auth_user_id) {
          this.createViewerPeer(data.offer);
        }
      });
    },

    createViewerPeer(incomingOffer) {
      const peer = new Peer({
        initiator: false,
        trickle: false,
        config: {
          iceServers: [
            {
              urls: "stun:stun.stunprotocol.org",
            },
            {
              urls: this.turn_url,
              username: this.turn_username,
              credential: this.turn_credential,
            },
          ],
        },
      });

      peer.addTransceiver("video", { direction: "recvonly" });
      this.handlePeerEvents(peer, incomingOffer);
    },

    handlePeerEvents(peer, incomingOffer) {
      peer.on("signal", (data) => {
        axios
          .post("/stream-answer", {
            answer: data,
            streamId: this.stream_id,
          })
          .then((res) => {
            console.log(res);
          })
          .catch((err) => {
            console.log(err);
          });
      });

      peer.on("stream", (stream) => {
        // display remote stream
        this.$refs.viewer.srcObject = stream;
      });

      peer.on("track", (track, stream) => {
        console.log("onTrack");
      });

      peer.on("connect", () => {
        console.log("Viewer Peer connected");
      });

      peer.on("close", () => {
        console.log("Viewer Peer closed");
      });

      peer.on("error", (err) => {
        console.log("handle error gracefully");
      });

      const updatedOffer = {
        ...incomingOffer,
        sdp: `${incomingOffer.sdp}\n`,
      };
      peer.signal(updatedOffer);
    },
  },
};
</script>

<style scoped>
</style>